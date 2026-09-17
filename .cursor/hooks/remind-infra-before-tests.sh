#!/usr/bin/env bash
# beforeShellExecution — lembrete se rodar pest/phpunit sem docker compose
set -euo pipefail

input=$(cat)
command=$(python3 -c "import json,sys; print(json.load(sys.stdin).get('command',''))" <<<"$input")

if [[ ! "$command" =~ (pest|phpunit|artisan[[:space:]]+test) ]]; then
  echo '{"permission":"allow"}'
  exit 0
fi

if [[ "$command" =~ Infrastructure|LocalServices ]]; then
  if ! curl -sf --max-time 1 http://127.0.0.1:4566/_localstack/health >/dev/null 2>&1; then
    echo '{
      "permission": "ask",
      "user_message": "Testes de infra (LocalServices) costumam exigir `docker compose up -d`. LocalStack não respondeu em :4566. Continuar mesmo assim?",
      "agent_message": "ShipSync: subir Redis, Memcached e LocalStack antes dos testes de infra."
    }'
    exit 0
  fi
fi

echo '{"permission":"allow"}'
exit 0
