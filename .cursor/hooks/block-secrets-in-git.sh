#!/usr/bin/env bash
# beforeShellExecution — evita commit acidental de segredos
set -euo pipefail

input=$(cat)
command=$(python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('command',''))" <<<"$input")

if [[ ! "$command" =~ git[[:space:]]+(add|commit) ]]; then
  echo '{"permission":"allow"}'
  exit 0
fi

if echo "$command" | grep -qE '\.env(\.|$)|credentials\.json|\.pem|id_rsa|secrets\.'; then
  python3 - <<'PY'
import json
print(json.dumps({
    "permission": "deny",
    "user_message": "Comando bloqueado: parece incluir arquivos sensíveis (.env, chaves, credentials).",
    "agent_message": "ShipSync hook: não adicionar/commitar segredos. Use .env.example e .gitignore.",
}))
PY
  exit 0
fi

echo '{"permission":"allow"}'
exit 0
