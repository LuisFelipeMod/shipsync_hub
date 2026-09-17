# Docker Compose (infra local)

## Por que usamos isso

- Um comando sobe **todos** os dependências do ShipSync em dev, com rede e healthchecks alinhados.
- Evita instalar Redis, Memcached e emulador AWS na máquina host.
- Serviço `app` (profile `dev`) permite Composer/Pest quando não há PHP local.

## Como aparece no ShipSync

Arquivo: [`docker-compose.yml`](../../../docker-compose.yml)

| Serviço | Porta | Função |
|---------|-------|--------|
| `redis` | 6379 | Session + queue Laravel (padrão) |
| `rabbitmq` | 5672 / 15672 | Filas AMQP opcionais + Management UI |
| `memcached` | 11211 | Cache de cotações |
| `localstack` | 4566 | SQS, S3, DynamoDB (+ Lambda on-demand) |
| `app` | — | PHP 8.4 CLI, volume do projeto, profile `dev` |
| `web` | 8000 | `artisan serve` — API + Swagger UI, profile `dev` |

Rede: `shipsync`. Volumes: `redis_data`, `localstack_data`.

Comandos: `docker compose up -d`, `./bin/serve`, `docker compose --profile dev run --rm app …`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| `docker.sock` inexistente | Daemon Docker parado — `sudo systemctl start docker` |
| Port already allocated | Outro processo/container na 6379/11211/4566 |
| `app` não sobe | Profile `dev` não ativado — usar `--profile dev` |
| Healthcheck LocalStack falha | Primeira subida lenta; aguardar ou ver logs do container |
