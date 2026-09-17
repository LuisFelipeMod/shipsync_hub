#!/usr/bin/env bash
# postToolUse — aciona skill shipsync-desenvolvimento quando objetivo do roadmap avança
set -euo pipefail

input=$(cat)

HOOK_JSON="$input" python3 - <<'PY'
import json
import os
import re
import sys
from datetime import date
from pathlib import Path

raw = os.environ.get("HOOK_JSON", "")
try:
    data = json.loads(raw)
except json.JSONDecodeError:
    sys.exit(0)

tool = (data.get("tool_name") or data.get("tool") or "").lower()
tool_input = data.get("tool_input") or data.get("input") or {}
path = (
    tool_input.get("path")
    or tool_input.get("file_path")
    or tool_input.get("target_file")
    or ""
).replace("\\", "/")

repo_root = Path.cwd()
dev_doc = repo_root / "docs" / "desenvolvimento.md"
skill_hint = (
    "Aplique a skill **shipsync-desenvolvimento** agora: leia `docs/desenvolvimento.md`, "
    "confirme checkboxes, atualize 'O que já foi desenvolvido', "
    "Histórico resumido e *Última atualização* (hoje: "
    + date.today().isoformat()
    + "). Não encerre a tarefa só com [x] se faltar histórico/data."
)

def count_checked(text: str) -> int:
    return len(re.findall(r"^\s*-\s*\[x\]", text, re.MULTILINE | re.IGNORECASE))

def emit(msg: str) -> None:
    print(json.dumps({"additional_context": msg}))

# --- Edição direta do roadmap ---
if tool in ("write", "strreplace", "applypatch", "edit"):
    new_content = tool_input.get("contents") or tool_input.get("new_string") or tool_input.get("content") or ""
    old_string = tool_input.get("old_string") or ""

    if path.endswith("docs/desenvolvimento.md"):
        triggered = False
        if re.search(r"-\s*\[\s*\]", old_string) and re.search(r"-\s*\[x\]", new_content, re.I):
            triggered = True
        if new_content:
            old_file = dev_doc.read_text(encoding="utf-8") if dev_doc.is_file() else ""
            if count_checked(new_content) > count_checked(old_file):
                triggered = True
        if triggered:
            if "*Última atualização:" not in new_content and "Histórico resumido" not in new_content:
                emit(
                    skill_hint
                    + " Detectado novo [x] em docs/desenvolvimento.md sem histórico/data visível nesta edição."
                )
            else:
                emit(
                    "Checkbox do roadmap atualizado. Revise com skill **shipsync-desenvolvimento**: "
                    "histórico e bullets em 'O que já foi desenvolvido' estão completos?"
                )
            sys.exit(0)

    # Entrega em código alinhada ao roadmap — lembrete leve (Domain/Infrastructure/CI)
    milestone_paths = (
        "app/Domain/",
        "app/Application/",
        "app/Infrastructure/",
        "tests/Domain/",
        ".github/workflows/",
    )
    if any(p in path for p in milestone_paths) and path.endswith((".php", ".yml", ".yaml")):
        if "Example" not in path and ".gitkeep" not in path:
            emit(
                "Possível conclusão de item do roadmap (arquivo: "
                + path
                + "). Skill **shipsync-desenvolvimento**: marcar [x], histórico e data em "
                "`docs/desenvolvimento.md` se a entrega estiver fechada."
            )
            sys.exit(0)

# --- Pest verde: sugerir sync do roadmap ---
if tool == "shell":
    command = tool_input.get("command") or ""
    output = (data.get("result") or data.get("output") or tool_input.get("output") or "")
    if isinstance(output, dict):
        output = output.get("stdout", "") + output.get("stderr", "")
    output = str(output)
    if re.search(r"pest|artisan\s+test|phpunit", command, re.I):
        if re.search(r"\bPASS\b|\d+\s+passed", output, re.I) and "failed" not in output.lower():
            if dev_doc.is_file() and re.search(r"-\s*\[\s\]", dev_doc.read_text(encoding="utf-8")):
                emit(
                    "Testes passaram. Se esta sessão fechou um objetivo do roadmap, "
                    "aplique **shipsync-desenvolvimento** em `docs/desenvolvimento.md` "
                    "(checkbox + histórico + data)."
                )
PY
