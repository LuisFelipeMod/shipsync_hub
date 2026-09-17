# Setup — ShipSync Hub

Objetivo, progresso e próximas etapas: [`desenvolvimento.md`](desenvolvimento.md).

Conceitos técnicos (co-piloto): [`.cursor/docs/README.md`](../.cursor/docs/README.md).

## Pré-requisitos

- Docker + Compose (`docker compose`)
- PHP **8.4+** no host é opcional: o fluxo padrão instala deps e roda Pest via container (`./bin/composer`, `./bin/test`)

## Dia a dia

```bash
docker compose up -d
./vendor/bin/pest
# ou, sem PHP no host:
./bin/test --in-container
```

Infra esperada: Redis `:6379`, Memcached `:11211`, RabbitMQ `:5672` (UI `:15672`), LocalStack `:4566`. O `.env` deve seguir o [`.env.example`](../.env.example) (Redis, Memcached, `AWS_ENDPOINT`, `RABBITMQ_*`).

## Infra local

```bash
docker compose up -d
curl -s http://localhost:4566/_localstack/health | head -c 200
```

## Docker — rebuild e recriação de containers

Serviços do projeto:

| Serviço | Profile | Imagem | Porta |
|---------|---------|--------|-------|
| `redis`, `memcached`, `localstack` | (padrão) | pull do registry | 6379, 11211, 4566 |
| `app`, `web` | `dev` | build [`docker/php/Dockerfile`](../docker/php/Dockerfile) | `web` → 8000 |

**Rebuild** = reconstruir a **imagem** PHP (necessário após mudar o `Dockerfile` ou extensões PECL).  
**Recriar** = substituir o **container** em execução (compose recria se a config mudou ou após `up --force-recreate`).

### Rebuild da imagem PHP (`app` / `web`)

Use quando alterou `docker/php/Dockerfile` ou o build falhou (ex.: extensão PHP no container):

```bash
# Rebuild completo, sem cache (mais lento, mais confiável após mudança no Dockerfile)
docker compose --profile dev build --no-cache web

# Rebuild incremental (aproveita cache de camadas)
docker compose --profile dev build web
```

Subir de novo com a imagem nova:

```bash
docker compose --profile dev up -d --force-recreate web
```

Atalho equivalente ao fluxo HTTP + Swagger:

```bash
./bin/serve
```

(`bin/serve` sobe infra base e faz `up -d --build web`.)

Rebuild só para comandos one-shot (`composer`, `artisan` via `app`):

```bash
docker compose --profile dev build app
```

`app` e `web` compartilham a **mesma** imagem; rebuild de um vale para os dois.

### Recriar containers (sem apagar volumes)

Aplica `docker-compose.yml` atualizado ou env novo, **mantendo** dados em `redis_data` e `localstack_data`:

```bash
# Infra + HTTP (profile dev)
docker compose up -d
docker compose --profile dev up -d --force-recreate

# Só o servidor Laravel / Swagger
docker compose --profile dev up -d --force-recreate web
```

Parar sem remover containers:

```bash
docker compose --profile dev stop web app
docker compose stop
```

Remover containers parados (volumes **permanecem**):

```bash
docker compose --profile dev down
docker compose down
```

### Recriar do zero (apaga volumes)

**Cuidado:** apaga fila/cache persistido no Redis, estado do LocalStack (filas SQS, tabela DynamoDB, bucket), etc. Use só quando quiser infra “limpa”:

```bash
docker compose --profile dev down -v
docker compose down -v
```

Depois suba de novo e, se necessário, rode migrate / confira init do LocalStack:

```bash
docker compose up -d
docker compose --profile dev up -d --build web
curl -s http://localhost:4566/_localstack/health | head -c 200
```

### Conferir estado

```bash
docker compose ps -a
docker compose --profile dev logs -f web
docker compose logs -f localstack
```

## Dependências PHP

Com PHP no host:

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Sem PHP no host (usa container `app` — **recomendado no ShipSync**):

```bash
cp .env.example .env
chmod +x bin/composer bin/test
docker compose up -d   # redis, memcached, localstack, rabbitmq…
./bin/composer update  # lock + vendor (inclui aws/aws-sdk-php) no volume montado
docker compose --profile dev run --rm app php artisan key:generate
docker compose --profile dev run --rm app touch database/database.sqlite
docker compose --profile dev run --rm app php artisan migrate
docker compose --profile dev up -d web
```

O serviço `web` usa o mesmo `./vendor` montado em `/var/www/html`. Sem `aws/aws-sdk-php` instalado, a API quebra com `Class "Aws\DynamoDb\DynamoDbClient" not found`.

### Permissão negada em `composer.lock` / `vendor/`

Se `./bin/composer` falhar com `Permission denied` ao gravar o lock, o volume foi criado com UID diferente do seu usuário (comum após rodar Composer como root ou `nobody`):

```bash
sudo chown -R "$(id -un):$(id -gn)" vendor composer.lock composer.json
./bin/composer update
docker compose --profile dev up -d --force-recreate web
```

### Fedora — extensões PHP (CLI)

O `php-cli` sozinho **não** traz `ext-dom` / `ext-xml`. Laravel, PHPUnit e Pest precisam delas.

```bash
sudo dnf install php-cli php-xml php-mbstring php-zip php-sqlite3 composer
php -m | grep -E '^(dom|xml|mbstring|zip|pdo_sqlite)$'
```

Se `composer install` avisar que o lock está desatualizado em relação ao `composer.json`:

```bash
composer update --lock
composer install
```

Evite `--ignore-platform-req=ext-dom` em dev: você ficaria sem extensões reais ao rodar Pest/Artisan.

Sem PHP local, Pest via imagem oficial:

```bash
docker compose up -d
docker run --rm -v "$PWD:/app" -w /app --network host php:8.4-cli ./vendor/bin/pest
```

O `composer.json` define `"audit": { "block-insecure": false }` para permitir lock em dev; revise advisories antes de produção.

## Documentação da API (Swagger)

### Docker (recomendado)

Com `.env` criado e dependências instaladas (`./bin/composer install`, `key:generate`, `migrate` — ver acima):

```bash
./bin/serve
# ou: docker compose up -d && docker compose --profile dev up -d --build web
```

| Recurso | URL |
|---------|-----|
| UI cotações (nova + consulta por ID) | http://localhost:8000/quotes — link direto: `/quotes/{uuid}` |
| Swagger UI | http://localhost:8000/api/documentation |
| OpenAPI YAML (v1) | http://localhost:8000/docs/openapi/v1/openapi.yaml |
| OpenAPI JSON | http://localhost:8000/docs/openapi/v1/openapi.json |
| RabbitMQ Management | http://localhost:15672 (user/senha dev: `shipsync`) |

O serviço `web` (profile `dev`) sobe `php artisan serve` na porta **8000**. Hosts de infra vêm de [`docker/env/web.env`](../docker/env/web.env) (sobrescreve `127.0.0.1` do `.env` do host) e de [`DockerEnvironmentOverrides`](../app/Infrastructure/Docker/DockerEnvironmentOverrides.php) ao detectar container. Jobs usam `QUEUE_CONNECTION=sync` no container.

Recrie o container após mudar compose/env Docker:

```bash
docker compose --profile dev up -d --force-recreate web
```

**`RedisException: Connection refused` na Swagger UI:** o `.env` montado no volume fixava `REDIS_HOST=127.0.0.1` **antes** do `LoadConfiguration` do Laravel. Overrides rodam no início de `bootstrap/app.php` + rotas OpenAPI sem sessão Redis. Recrie o `web` após pull.

Parar: `docker compose --profile dev stop web`

### Host (`php artisan serve`)

Com `OPENAPI_UI_ENABLED=true` no `.env`:

| Recurso | URL |
|---------|-----|
| Swagger UI | http://localhost:8000/api/documentation |

A spec versionada fica no repositório em [`docs/openapi/v1/openapi.yaml`](openapi/v1/openapi.yaml) (contrato alinhado a `/api/v1`). Em produção, mantenha `OPENAPI_UI_ENABLED=false`.

## New Relic (opcional)

- Variáveis: `NEW_RELIC_ENABLED`, `NEW_RELIC_APP_NAME`, `NEW_RELIC_LICENSE_KEY` no `.env`.
- Sem extensão PHP `newrelic` em local, o app usa tracer NoOp (sem erro).
- Middleware [`ObserveHttpRequests`](../app/Http/Middleware/ObserveHttpRequests.php) nomeia transações HTTP quando o agente está ativo.

## DLQ SQS (monitorar e reprocessar)

Com LocalStack no ar e filas provisionadas pelo init:

```bash
php artisan shipsync:dlq:monitor
php artisan shipsync:dlq:monitor --peek=5
php artisan shipsync:dlq:reprocess --limit=10
```

Fluxo manual: corrigir o bug → `monitor` → `reprocess` com limite pequeno → validar fila principal.

## RabbitMQ (workers Laravel)

1. Subir compose (serviço `rabbitmq`).
2. `./bin/composer update` (driver `vladimir-yuldashev/laravel-queue-rabbitmq`).
3. No `.env`: `QUEUE_CONNECTION=rabbitmq` (opcional; padrão do projeto continua `redis`).
4. Worker: `php artisan queue:work rabbitmq --queue=shipsync-jobs`.

Detalhes: [`.cursor/docs/rabbitmq/`](../.cursor/docs/rabbitmq/README.md).

## Testes

Infra no ar + dependências instaladas:

```bash
# Host (127.0.0.1 nas portas publicadas)
./vendor/bin/pest
./vendor/bin/pest --filter=LocalServices

# Ou via container (hosts = nomes dos serviços Docker)
./bin/test --in-container --filter=LocalServices
```
