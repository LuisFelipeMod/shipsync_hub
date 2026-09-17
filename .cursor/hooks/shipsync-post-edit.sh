#!/usr/bin/env bash
# postToolUse — lembretes de arquitetura/TDD/docs após Write ou StrReplace
set -euo pipefail

input=$(cat)

python3 - <<'PY'
import json
import re
import sys

raw = sys.stdin.read()
try:
    data = json.loads(raw)
except json.JSONDecodeError:
    sys.exit(0)

tool = (data.get("tool_name") or data.get("tool") or "").lower()
if tool not in ("write", "strreplace", "applypatch", "edit"):
    sys.exit(0)

tool_input = data.get("tool_input") or data.get("input") or {}
path = (
    tool_input.get("path")
    or tool_input.get("file_path")
    or tool_input.get("target_file")
    or ""
)
content = tool_input.get("contents") or tool_input.get("new_string") or tool_input.get("content") or ""
if not path and isinstance(tool_input.get("files"), list) and tool_input["files"]:
    path = tool_input["files"][0].get("path", "")

if not path:
    sys.exit(0)

path_norm = path.replace("\\", "/")
hints = []

# TDD: nova implementação PHP fora de tests/ sem teste correspondente
if path_norm.endswith(".php") and "/tests/" not in path_norm and path_norm.startswith(
    ("app/", "src/")
):
    if re.search(r"\b(class|function)\b", content):
        hints.append(
            "ShipSync TDD: se for nova funcionalidade, o teste Pest deve ter sido escrito "
            "antes (Feature/Unit/Domain). Rode `./vendor/bin/pest` após implementar."
        )

# Domínio acoplado a AWS/Laravel facade
if "/app/Domain/" in path_norm or path_norm.startswith("app/Domain/"):
    if re.search(r"Aws\\|Illuminate\\|Facades\\|SqsClient|DynamoDbClient|S3Client", content):
        hints.append(
            "ShipSync arquitetura: `app/Domain` não deve importar AWS SDK nem Facades. "
            "Use interfaces e adapters em `app/Infrastructure`."
        )

# Infrastructure AWS sem menção a endpoint/config
if path_norm.endswith(".php") and "/Infrastructure/" in path_norm:
    if re.search(r"Aws\\|SqsClient|DynamoDbClient|S3Client", content):
        if not re.search(r"AWS_ENDPOINT|aws\.endpoint|config\s*\(\s*['\"]services\.aws", content, re.I):
            hints.append(
                "ShipSync AWS: clientes AWS devem usar config centralizada com `AWS_ENDPOINT` "
                "(LocalStack vs prod). Ver skill shipsync-aws."
            )

# Conceitos que pedem doc em .cursor/docs
concept_map = [
    (r"CircuitBreaker|circuit breaker", "circuit-breaker"),
    (r"DeadLetter|DLQ|dead.?letter", "dlq"),
    (r"Strategy|implements.*Quote", "strategy"),
    (r"Adapter|CarrierQuote", "adapter"),
    (r"Backoff|Jitter|exponential", "backoff-jitter"),
    (r"LambdaClient|invoke\s*\(", "lambda"),
]
for pattern, folder in concept_map:
    if re.search(pattern, content, re.I):
        hints.append(
            f"ShipSync docs: ao introduzir este conceito, criar/atualizar "
            f"`.cursor/docs/{folder}/README.md` (2–3 bullets 'por que' + erros comuns)."
        )
        break

if not hints:
    sys.exit(0)

print(json.dumps({"additional_context": "\n".join(hints)}))
PY
