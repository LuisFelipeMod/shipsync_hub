# Controle de desenvolvimento — ShipSync Hub

Documento de **continuidade** para novas sessões (humano ou agente). Atualize este arquivo quando uma etapa relevante for concluída.

Referências: prompt original [`p-1.md`](../p-1.md), setup [`setup.md`](setup.md), pastas [`arquitetura-pastas.md`](arquitetura-pastas.md), conceitos [`.cursor/docs/`](../.cursor/docs/README.md).

---

## Objetivo do projeto

**ShipSync Hub** é um **gateway de logística e fretes**: integra transportadoras, orquestra cotações e fluxos assíncronos, com foco em **resiliência**, **escalabilidade** e **observabilidade**.

Metas de processo (do prompt inicial):

- Entregar valor **rápido**, com **TDD (Pest)** e **Clean Architecture** (`app/Domain` isolado).
- **Aprender na prática** — padrões (Strategy, Adapter, Circuit Breaker, DLQ, Backoff) documentados em `.cursor/docs/<tópico>/`.
- **AWS abstraída** via SDK + `AWS_ENDPOINT` (LocalStack em dev, AWS real em prod).
- Co-piloto como **mentor**: teste antes do código, revisão SOLID/performance quando o Luis enviar trechos.

### Stack alvo (visão completa)

| Área | Tecnologia |
|------|------------|
| Back-end | PHP 8.4+, Laravel 11+, SOLID, Strategy, Adapter |
| Sessão / filas Laravel | Redis |
| Cache de cotações | Memcached |
| Mensageria | AWS SQS + **DLQ** |
| Persistência de negócio | DynamoDB |
| Arquivos | S3 |
| Compute (event-driven) | AWS Lambda |
| Mensageria local / workers | RabbitMQ (concorrência e filas complementares ao Redis/SQS) |
| Resiliência | Circuit Breaker, Exponential Backoff + Jitter |
| Observabilidade | New Relic (APM/tracing) |
| Dev AWS | LocalStack |
| Testes / CI | Pest (TDD/BDD), GitHub Actions |
| Documentação HTTP | OpenAPI 3 + Swagger UI |

**Fora do escopo inicial acordado:** MySQL/Postgres no compose — SQLite só para internals Laravel; negócio no DynamoDB.

---

## O que já foi desenvolvido (bootstrap)

Estado atual: **Fases 6 e 7 concluídas**. Próximo foco: **Fase 8** (CI/CD e deploy).

### Infra local

- [`docker-compose.yml`](../docker-compose.yml): Redis, Memcached, RabbitMQ, LocalStack; serviços `app` e `web` (profile `dev`, PHP 8.4); [`bin/serve`](../bin/serve) expõe UI e Swagger na porta 8000.
- [`localstack/init/ready.d/01-init-aws.sh`](../localstack/init/ready.d/01-init-aws.sh): fila `shipsync-jobs`, DLQ `shipsync-jobs-dlq`, bucket `shipsync-local`, tabela `shipsync-records`.
- [`docker/php/Dockerfile`](../docker/php/Dockerfile), [`bin/composer`](../bin/composer), [`bin/test`](../bin/test).
- `.env` do dev alinhado ao [`.env.example`](../.env.example) (Redis, Memcached, `AWS_ENDPOINT`). Dependências PHP via container (`vendor/` no volume).

### Aplicação Laravel

- Laravel **11** + Pest **3**, PHP **≥ 8.4** (`composer.json` / lock).
- [`.env.example`](../.env.example): Redis, Memcached, `AWS_ENDPOINT`, `SQS_DLQ`, `NEW_RELIC_*`, `RABBITMQ_*`.
- [`config/services.php`](../config/services.php): bloco `aws` centralizado.
- [`app/Domain/Shipping/`](../app/Domain/Shipping/): VOs (`Cep`, `Money`, `Weight`, `Dimensions`, `Package`), `QuoteRequest`/`Quote`/`QuoteResult`, ports `CarrierQuotePort` e `QuoteRepositoryPort`.
- [`app/Application/Messaging/`](../app/Application/Messaging/): port `MessageQueuePort` + DTO `ReceivedMessage`.
- [`app/Infrastructure/Aws/`](../app/Infrastructure/Aws/): `AwsClientFactory`, `SqsMessageQueue`, `SqsDeadLetterQueueService`, `DynamoDbQuoteRepository`, `ShippingQuoteRecordMapper` (SDK + `config('services.aws')`).
- Comandos DLQ: `shipsync:dlq:monitor`, `shipsync:dlq:reprocess`.
- [`config/newrelic.php`](../config/newrelic.php) + [`ObserveHttpRequests`](../app/Http/Middleware/ObserveHttpRequests.php): APM New Relic (NoOp sem agente).
- [`config/queue.php`](../config/queue.php): conexão `rabbitmq` (`vladimir-yuldashev/laravel-queue-rabbitmq`).
- UI: [`layouts/shipsync`](../resources/views/layouts/shipsync.blade.php), [`/quotes`](../routes/web.php) consumindo API v1 via fetch.
- Dependência [`aws/aws-sdk-php`](../composer.json) no `composer.json` — rodar `./bin/composer update` após pull.
- [`app/Providers/AppServiceProvider.php`](../app/Providers/AppServiceProvider.php): bindings dos ports AWS, carrier stub, cache de cotação.
- [`app/Application/Shipping/`](../app/Application/Shipping/): `RequestShippingQuoteUseCase`, `QuoteCachePort`, factory e presenter.
- [`routes/api.php`](../routes/api.php): `POST/GET /api/v1/shipping/quotes` + job [`ProcessShippingQuoteJob`](../app/Jobs/ProcessShippingQuoteJob.php) (fila Redis).
- [`app/Console/Commands/RequestShippingQuoteCommand.php`](../app/Console/Commands/RequestShippingQuoteCommand.php): `shipping:quote` (caso de uso síncrono).
- [`app/Infrastructure/Carriers/StubCarrierQuoteAdapter.php`](../app/Infrastructure/Carriers/StubCarrierQuoteAdapter.php): cotação fake até HTTP real.
- [`config/shipping.php`](../config/shipping.php): TTL do cache Memcached (`SHIPPING_QUOTE_CACHE_TTL`) + resiliência (`SHIPPING_CARRIER_*`).
- [`app/Infrastructure/Resilience/`](../app/Infrastructure/Resilience/) + [`ResilientCarrierQuoteAdapter`](../app/Infrastructure/Carriers/ResilientCarrierQuoteAdapter.php): circuit breaker, backoff/jitter e retries em `CarrierQuotePort`.
- [`docs/openapi/v1/openapi.yaml`](../docs/openapi/v1/openapi.yaml): contrato OpenAPI 3 alinhado a `/api/v1`.
- [`config/openapi.php`](../config/openapi.php) + [`OpenApiDocumentationController`](../app/Http/Controllers/OpenApiDocumentationController.php): Swagger UI (`/api/documentation`) e spec YAML/JSON.

### Testes

- [`tests/Infrastructure/LocalServicesHealthTest.php`](../tests/Infrastructure/LocalServicesHealthTest.php): Redis, Memcached, RabbitMQ AMQP, health LocalStack.
- [`tests/Infrastructure/Aws/`](../tests/Infrastructure/Aws/): round-trip SQS e persistência DynamoDB no LocalStack.
- [`tests/Domain/Shipping/`](../tests/Domain/Shipping/): regras de cotação sem boot Laravel.
- [`tests/Pest.php`](../tests/Pest.php): Laravel boot só em `Feature/`.
- [`tests/Feature/Shipping/ShippingQuoteApiTest.php`](../tests/Feature/Shipping/ShippingQuoteApiTest.php): API + job (fila `sync` em testes).
- [`tests/Unit/Application/Shipping/`](../tests/Unit/Application/Shipping/): caso de uso com doubles.
- [`tests/Feature/OpenApi/OpenApiDocumentationTest.php`](../tests/Feature/OpenApi/OpenApiDocumentationTest.php): UI, spec v1 e alinhamento com rotas.
- [`tests/Unit/Infrastructure/Resilience/`](../tests/Unit/Infrastructure/Resilience/): breaker, backoff e adapter resiliente.
- [`tests/Infrastructure/Aws/SqsDeadLetterQueueTest.php`](../tests/Infrastructure/Aws/SqsDeadLetterQueueTest.php): reprocess DLQ.
- [`tests/Feature/Shipping/ShippingQuoteUiTest.php`](../tests/Feature/Shipping/ShippingQuoteUiTest.php): layout web de cotações.
- Suite verde com infra up + SDK instalado (Domain + Feature + Infrastructure).

### Automação e documentação

- **Skills:** `.cursor/skills/shipsync-{hub,tdd,architecture,aws,concept-docs,desenvolvimento}`.
- **Hooks:** `validate-commit-safe.sh` (commit/add + pedido de commit no chat), lembrete infra Pest, pós-edição TDD/AWS, sync roadmap → skill `shipsync-desenvolvimento`.
- **Conceitos:** [`.cursor/docs/`](../.cursor/docs/README.md) (compose, redis, memcached, localstack, dlq, ports, etc.).
- [`docs/setup.md`](setup.md): dia a dia Docker-first + Pest.

### Ainda não implementado (stack alvo)

- Adapters de transportadoras HTTP (substituir stub).
- Adapter S3 e consumer SQS de longa duração (worker dedicado).
- Lambda local/prod.
- GitHub Actions (CI) e pipeline deploy.

---

## Próximas etapas (ordem sugerida)

Use **TDD**: Pest primeiro, implementação depois. Marque `[x]` aqui ao concluir.

### Fase 1 — Ambiente de dev confiável

- [x] Garantir `composer install` no host (Fedora: `php-xml`, `php-mbstring`, etc.) ou fluxo `./bin/composer`.
- [x] `.env` alinhado ao `.env.example` (Redis/Memcached/drivers).
- [x] `docker compose up -d` documentado no dia a dia; CI local: `./vendor/bin/pest`.

### Fase 2 — Primeiro vertical slice (domínio)

- [x] Modelar entidades/value objects iniciais em `app/Domain` (ex.: pedido de cotação, resultado).
- [x] Definir **ports** (interfaces) para cotação externa e persistência.
- [x] Testes **Unit/Domain** sem Laravel.

### Fase 3 — Infraestrutura AWS (LocalStack)

- [x] Adapter SQS (publicar/consumir) usando `config('services.aws')`.
- [x] Repositório DynamoDB para entidade principal.
- [x] Documentar padrão em `.cursor/docs/` (ex.: `strategy`, `adapter`) ao introduzir.

### Fase 4 — API e jobs

- [x] Endpoint ou comando artisan fino → caso de uso.
- [x] Job Laravel + fila Redis; depois migrar/publicar also SQS se fizer sentido arquitetural.
- [x] Cache Memcached para respostas de cotação (TTL).

### Fase 5 — Documentação com Swagger

- [x] Spec OpenAPI 3 versionada + config (`config/openapi.php`); UI Swagger local (YAML canônico em `docs/openapi/v1/`).
- [x] Documentar `POST/GET /api/v1/shipping/quotes` (request, respostas 202/200/404/422, exemplos).
- [x] Expor Swagger UI em ambiente local (rota documentada em [`setup.md`](setup.md)).
- [x] Garantir que a spec OpenAPI versiona junto com `/api/v1` (prefixo e breaking changes explícitos).

### Fase 6 — Resiliência e observabilidade

- [x] Circuit Breaker + Backoff/Jitter em chamadas HTTP a carriers.
- [x] New Relic (env, middleware/spans).
- [x] DLQ: monitoramento e reprocessamento manual documentado.
- [x] RabbitMQ: serviço no compose, config Laravel (ou bridge) e documentação de workers/concorrência local.

### Fase 7 — Layout Laravel (uso da API)

- [x] Layout base Blade (ou stack front acordada) alinhado ao produto ShipSync.
- [x] Telas/fluxos para solicitar e consultar cotações consumindo `/api/v1/shipping/quotes`.
- [x] Rotas web, auth mínima se necessário, e registro em [`setup.md`](setup.md).

### Fase 8 — CI/CD e produção

- [ ] GitHub Actions: Pest + lint (Pint) em PR.
- [ ] Pipeline deploy; `AWS_ENDPOINT` vazio em prod.

---

## Regras para novas janelas de contexto

1. Ler **este arquivo** + [`p-1.md`](../p-1.md) se o escopo for amplo.
2. Seguir skills em [`.cursor/skills/shipsync-hub/`](../.cursor/skills/shipsync-hub/SKILL.md). Resumo rápido: skill [`shipsync-status`](../.cursor/skills/shipsync-status/SKILL.md).
3. Novo padrão ou infra → `.cursor/docs/<tópico>/README.md` (skill `shipsync-concept-docs`).
4. Objetivo concluído → skill [`shipsync-desenvolvimento`](../.cursor/skills/shipsync-desenvolvimento/SKILL.md) (hook dispara ao marcar `[x]` ou após testes verdes).
5. Não commitar `.env`; hooks bloqueiam tentativas comuns.

---

## Histórico resumido

| Data (aprox.) | Entrega |
|---------------|---------|
| 2026-09-16 | Bootstrap: compose, LocalStack init, teste infra Pest, Laravel+Pest, skills/hooks, docs conceito + setup |
| 2026-09-16 | Fase 1 concluída: `.env` alinhado, compose no ar, Pest verde (fluxo Docker-first) |
| 2026-09-16 | Fase 2 concluída: VOs/ports de cotação em `app/Domain/Shipping` + suite `tests/Domain` |
| 2026-09-16 | Fase 3 concluída: adapters SQS/DynamoDB, testes Infrastructure/Aws, doc `.cursor/docs/adapter/` |
| 2026-09-16 | Fase 4 concluída: API v1 cotação, job Redis, cache Memcached, comando `shipping:quote`, testes Feature/Unit Application |
| 2026-09-16 | Roadmap: nova Fase 5 (Swagger/OpenAPI); resiliência → Fase 6; CI/CD → Fase 7 |
| 2026-09-16 | Fase 5 concluída: spec `docs/openapi/v1`, Swagger UI, testes de alinhamento com rotas |
| 2026-09-17 | Fase 6 (parcial): circuit breaker + backoff/jitter no `CarrierQuotePort`, testes Unit/Resilience |
| 2026-09-17 | Roadmap: RabbitMQ na Fase 6 (substitui BullMQ); nova Fase 7 (layout Laravel para API); CI/CD → Fase 8 |
| 2026-09-17 | Fase 6 concluída: New Relic (middleware/spans), DLQ monitor/reprocess, RabbitMQ no compose |
| 2026-09-17 | Fase 7 concluída: layout Blade `/quotes` consumindo API v1 |

*Última atualização: 2026-09-17.*
