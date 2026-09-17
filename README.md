# ShipSync Hub

Gateway de **logística e fretes**: orquestra cotações de transportadoras, cache, filas assíncronas e persistência na AWS — com **Laravel 11**, **Clean Architecture** e testes **Pest (TDD)**.

Em desenvolvimento local, a AWS roda via **LocalStack** (SQS, DLQ, DynamoDB, S3); em produção, o mesmo código usa a AWS real (`AWS_ENDPOINT` vazio).

---

## O que você encontra aqui

- API REST versionada (`/api/v1/shipping/quotes`) + **OpenAPI 3** e **Swagger UI**
- UI web simples para solicitar e consultar cotações (`/quotes`)
- Domínio isolado em `app/Domain` (CEP, pacote, dinheiro, ports de cotação)
- Infra local com **Docker Compose**: Redis, Memcached, RabbitMQ, LocalStack
- Padrões de **resiliência** (circuit breaker, backoff, DLQ) e observabilidade (New Relic opcional)

---

## Pré-requisitos

| Obrigatório | Opcional |
|-------------|----------|
| [Docker](https://docs.docker.com/get-docker/) + Docker Compose (`docker compose`) | PHP **8.4+** e Composer no host (atalho alternativo abaixo) |
| Git | — |

O fluxo recomendado **não exige PHP instalado na máquina**: `./bin/composer`, `./bin/test` e `./bin/serve` usam containers.

---

## Primeira execução (passo a passo)

Clone o repositório e entre na pasta do projeto:

```bash
git clone <url-do-repositorio> shipsync_hub
cd shipsync_hub
```

### 1. Ambiente

```bash
cp .env.example .env
chmod +x bin/composer bin/test bin/serve
```

O [`.env.example`](.env.example) já aponta para Redis, Memcached, LocalStack e filas locais. Não commite o arquivo `.env`.

### 2. Subir a infraestrutura

```bash
docker compose up -d
```

Isso sobe Redis, Memcached, RabbitMQ e LocalStack (filas SQS, DLQ, bucket S3 e tabela DynamoDB são criados pelo script em [`localstack/init/ready.d/`](localstack/init/ready.d/)).

Confira se o LocalStack respondeu (opcional):

```bash
curl -s http://localhost:4566/_localstack/health | head -c 200
```

### 3. Dependências PHP e Laravel

```bash
./bin/composer update
```

Isso instala pacotes (incluindo `aws/aws-sdk-php`) em `vendor/` no volume do projeto.

Em seguida, prepare o Laravel (SQLite interno + chave da aplicação):

```bash
docker compose --profile dev run --rm app php artisan key:generate
docker compose --profile dev run --rm app touch database/database.sqlite
docker compose --profile dev run --rm app php artisan migrate
```

### 4. Subir HTTP (API + UI + Swagger)

```bash
./bin/serve
```

Abra no navegador:

| Recurso | URL |
|---------|-----|
| Health | http://localhost:8000/up |
| UI de cotações | http://localhost:8000/quotes |
| Swagger UI | http://localhost:8000/api/documentation |
| OpenAPI YAML (v1) | http://localhost:8000/docs/openapi/v1/openapi.yaml |
| RabbitMQ Management (dev) | http://localhost:15672 — usuário/senha: `shipsync` |

### 5. Rodar os testes

Com a infra no ar:

```bash
./bin/test --in-container
```

Ou, se tiver PHP 8.4+ no host e `vendor/` instalado:

```bash
./vendor/bin/pest
```

**Pronto.** Nos próximos dias, basta `docker compose up -d` e `./bin/serve` (veja [Comandos do dia a dia](#comandos-do-dia-a-dia)).

---

## Comandos do dia a dia

```bash
# Infra (Redis, Memcached, RabbitMQ, LocalStack)
docker compose up -d

# API + Swagger na porta 8000
./bin/serve

# Testes (recomendado: hosts Docker corretos)
./bin/test --in-container

# Instalar/atualizar dependências PHP
./bin/composer install   # ou: ./bin/composer update

# Artisan via container
docker compose --profile dev run --rm app php artisan <comando>
```

Parar o servidor HTTP:

```bash
docker compose --profile dev stop web
```

Logs do container web:

```bash
docker compose --profile dev logs -f web
```

---

## Primeira execução com PHP no host

Se preferir Composer e Pest direto na máquina (Fedora/Debian: instale `php-xml`, `php-mbstring`, `php-zip`, `php-sqlite3` — detalhes em [`docs/setup.md`](docs/setup.md)):

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
docker compose up -d
docker compose --profile dev up -d web
# ou: php artisan serve  (com OPENAPI_UI_ENABLED=true no .env)
./vendor/bin/pest
```

---

## Stack (visão geral)

| Área | Tecnologia |
|------|------------|
| Back-end | PHP 8.4+, Laravel 11+ |
| Arquitetura | Clean Architecture — `Domain` / `Application` / `Infrastructure` |
| Sessão e filas Laravel | Redis |
| Cache de cotações | Memcached |
| Mensageria | AWS SQS + DLQ (LocalStack em dev) |
| Persistência de negócio | DynamoDB |
| Arquivos | S3 |
| Testes | Pest 3 |
| Contrato HTTP | OpenAPI 3 — [`docs/openapi/v1/`](docs/openapi/v1/) |

Dados de negócio **não** usam MySQL/Postgres no compose; o SQLite serve só aos internals do Laravel (migrations padrão).

---

## Documentação do projeto

Use este README para **começar**. Aprofunde nos guias abaixo.

### Operacional e continuidade

| Documento | Conteúdo |
|-----------|----------|
| [**docs/setup.md**](docs/setup.md) | Setup completo: rebuild Docker, volumes, Swagger, DLQ, RabbitMQ, New Relic, troubleshooting |
| [**docs/desenvolvimento.md**](docs/desenvolvimento.md) | Objetivo, o que já foi feito, roadmap e fases |
| [**docs/arquitetura-pastas.md**](docs/arquitetura-pastas.md) | Onde colocar código (camadas, árvore do repo, convenções) |
| [**docs/openapi/v1/README.md**](docs/openapi/v1/README.md) | Contrato da API v1 |

### Conceitos técnicos (padrões e infra)

Índice completo: [**.cursor/docs/README.md**](.cursor/docs/README.md)

| Tópico | Link |
|--------|------|
| Clean Architecture | [`.cursor/docs/clean-architecture/`](.cursor/docs/clean-architecture/README.md) |
| Ports e adapters | [`.cursor/docs/ports/`](.cursor/docs/ports/README.md), [`.cursor/docs/adapter/`](.cursor/docs/adapter/README.md) |
| Docker Compose local | [`.cursor/docs/docker-compose/`](.cursor/docs/docker-compose/README.md) |
| LocalStack / `AWS_ENDPOINT` | [`.cursor/docs/localstack/`](.cursor/docs/localstack/README.md), [`.cursor/docs/aws-endpoint/`](.cursor/docs/aws-endpoint/README.md) |
| DLQ | [`.cursor/docs/dlq/`](.cursor/docs/dlq/README.md) |
| Circuit breaker | [`.cursor/docs/circuit-breaker/`](.cursor/docs/circuit-breaker/README.md) |
| Redis / Memcached / RabbitMQ | [`.cursor/docs/redis/`](.cursor/docs/redis/README.md), [`.cursor/docs/memcached/`](.cursor/docs/memcached/README.md), [`.cursor/docs/rabbitmq/`](.cursor/docs/rabbitmq/README.md) |
| Pest / TDD | [`.cursor/docs/pest-tdd/`](.cursor/docs/pest-tdd/README.md) |
| OpenAPI | [`.cursor/docs/openapi/`](.cursor/docs/openapi/README.md) |
| New Relic | [`.cursor/docs/newrelic/`](.cursor/docs/newrelic/README.md) |

### Co-piloto Cursor (opcional)

Regras e skills para agentes: [`.cursor/skills/`](.cursor/skills/) · hooks em [`.cursor/hooks/`](.cursor/hooks/).

---

## API em uma linha

- **Criar cotação:** `POST /api/v1/shipping/quotes` (corpo JSON com origem, destino, pacote) — resposta **202** com ID para consulta assíncrona.
- **Consultar:** `GET /api/v1/shipping/quotes/{id}`

Exemplos e schemas: Swagger UI ou [`docs/openapi/v1/openapi.yaml`](docs/openapi/v1/openapi.yaml).

Comando CLI equivalente (síncrono, para debug):

```bash
docker compose --profile dev run --rm app php artisan shipping:quote
```

---

## Problemas comuns

| Sintoma | O que fazer |
|---------|-------------|
| `Class "Aws\DynamoDb\DynamoDbClient" not found` | Rode `./bin/composer update` e recrie o container web |
| `Permission denied` em `composer.lock` / `vendor/` | Ajuste dono do volume: `sudo chown -R "$(id -un):$(id -gn)" vendor composer.lock` — ver [setup.md](docs/setup.md) |
| `RedisException: Connection refused` no Swagger | Recrie o web: `docker compose --profile dev up -d --force-recreate web` |
| Testes de infra falham | Confirme `docker compose up -d` e use `./bin/test --in-container --filter=LocalServices` |
| Infra “suja” ou filas estranhas | Reset com cuidado: `docker compose down -v` (apaga volumes) — detalhes em [setup.md](docs/setup.md) |

Mais cenários (Fedora/PHP, DLQ, workers RabbitMQ): [**docs/setup.md**](docs/setup.md).

---

## Estrutura rápida do código

```
app/Domain/          # Regras de negócio e ports (sem Laravel/AWS)
app/Application/     # Casos de uso
app/Infrastructure/  # AWS, transportadoras, resiliência
app/Http/            # Controllers finos
routes/api.php       # API v1
tests/               # Domain (sem Laravel), Feature, Infrastructure, Unit
```

Diagrama e regras de pasta: [**docs/arquitetura-pastas.md**](docs/arquitetura-pastas.md).

---

## Licença

MIT — ver [`composer.json`](composer.json).
