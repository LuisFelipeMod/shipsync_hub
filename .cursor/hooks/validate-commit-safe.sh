#!/usr/bin/env bash
# Modos: shell (beforeShellExecution git add/commit) | submit (beforeSubmitPrompt)
set -euo pipefail

mode="${1:-shell}"
input=$(cat)

HOOK_JSON="$input" HOOK_MODE="$mode" python3 - <<'PY'
import json
import os
import re
import subprocess
import sys

raw = os.environ.get("HOOK_JSON", "")
mode = os.environ.get("HOOK_MODE", "shell")

try:
    data = json.loads(raw) if raw else {}
except json.JSONDecodeError:
    print(json.dumps({"permission": "allow"}))
    sys.exit(0)

prompt = (
    data.get("prompt")
    or data.get("text")
    or data.get("content")
    or data.get("user_message")
    or data.get("message")
    or ""
)
command = data.get("command") or ""

COMMIT_INTENT = re.compile(
    r"\b(commit|committar|git\s+commit|fazer\s+commit|criar\s+commit|salvar\s+no\s+git)\b",
    re.I,
)

SENSITIVE_RULES: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"(^|/)\.env$"), ".env"),
    (re.compile(r"\.env\.(?!example(?:\.|$))"), ".env.* (exceto .env.example)"),
    (re.compile(r"(^|/)auth\.json$"), "auth.json (Composer)"),
    (re.compile(r"(^|/)credentials\.json$"), "credentials.json"),
    (re.compile(r"\.pem$"), "certificado .pem"),
    (re.compile(r"\.p8$"), "chave .p8"),
    (re.compile(r"(^|/)id_rsa$"), "chave privada id_rsa"),
    (re.compile(r"(^|/)database/.*\.sqlite(-journal)?$"), "SQLite local"),
    (re.compile(r"(^|/)storage/.*\.key$"), "chave em storage/"),
]


def run(*args: str) -> subprocess.CompletedProcess[str]:
    return subprocess.run(args, capture_output=True, text=True, cwd=os.getcwd())


def git_ok() -> bool:
    return run("git", "rev-parse", "--is-inside-work-tree").returncode == 0


def is_sensitive(path: str) -> str | None:
    normalized = path.replace("\\", "/").lstrip("./")
    if normalized in (".env.example",) or normalized.endswith("/.env.example"):
        return None
    for pattern, label in SENSITIVE_RULES:
        if pattern.search(normalized):
            return label
    return None


def is_ignored(path: str) -> bool:
    return run("git", "check-ignore", "-q", "--", path).returncode == 0


def is_tracked(path: str) -> bool:
    return run("git", "ls-files", "--error-unmatch", "--", path).returncode == 0


def staged_paths() -> list[str]:
    proc = run("git", "diff", "--cached", "--name-only", "--diff-filter=ACMR")
    if proc.returncode != 0:
        return []
    return [p.strip() for p in proc.stdout.splitlines() if p.strip()]


def status_paths() -> list[str]:
    proc = run("git", "status", "--porcelain", "-uall")
    if proc.returncode != 0:
        return []
    paths: list[str] = []
    for line in proc.stdout.splitlines():
        if len(line) < 4:
            continue
        path = line[3:].strip()
        if " -> " in path:
            path = path.split(" -> ", 1)[1]
        paths.append(path)
    return paths


def paths_from_add_command(cmd: str) -> list[str]:
    if not re.search(r"\bgit\s+add\b", cmd):
        return []
    tokens = cmd.split()
    try:
        idx = tokens.index("add")
    except ValueError:
        return []
    args = tokens[idx + 1 :]
    if not args or set(args) & {"-A", "--all", ".", "*"}:
        return status_paths()
    paths: list[str] = []
    for arg in args:
        if arg.startswith("-"):
            continue
        paths.append(arg)
    return paths


def analyze(paths: list[str], staged: set[str]) -> list[str]:
    issues: list[str] = []
    seen: set[str] = set()
    for path in paths:
        if path in seen:
            continue
        seen.add(path)
        label = is_sensitive(path)
        if not label:
            continue
        ignored = is_ignored(path)
        indexed = path in staged or is_tracked(path)
        if not ignored:
            issues.append(f"{path} — {label}; não coberto pelo .gitignore.")
        elif indexed:
            issues.append(
                f"{path} — {label}; está no .gitignore mas indexado/rastreado (git rm --cached)."
            )
    return issues


def deny(issues: list[str], context: str) -> None:
    body = "\n".join(f"- {i}" for i in issues)
    print(
        json.dumps(
            {
                "permission": "deny",
                "user_message": (
                    "Commit bloqueado (" + context + "): risco de segredo ou arquivo sensível.\n" + body
                ),
                "agent_message": (
                    "ShipSync validate-commit-safe: corrija .gitignore, "
                    "git rm --cached ou desstage. Use skill commit."
                ),
            },
            ensure_ascii=False,
        )
    )
    sys.exit(0)


def allow() -> None:
    print(json.dumps({"permission": "allow"}))
    sys.exit(0)


if not git_ok():
    allow()

staged = set(staged_paths())

if mode == "submit":
    if not COMMIT_INTENT.search(prompt):
        allow()
    issues = analyze(staged_paths() + status_paths(), staged)
    if issues:
        deny(issues, "pedido de commit no chat")
    allow()

if not re.search(r"\bgit\s+(add|commit)\b", command):
    allow()

paths: list[str] = []
if re.search(r"\bgit\s+commit\b", command):
    paths.extend(staged_paths())
if re.search(r"\bgit\s+add\b", command):
    paths.extend(paths_from_add_command(command))

issues = analyze(list(dict.fromkeys(paths)), staged)
if issues:
    deny(issues, "git add/commit")

allow()
PY
