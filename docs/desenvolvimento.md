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
| Assincronismo local (futuro) | BullMQ sobre Redis (concorrência limitada) |
| Resiliência | Circuit Breaker, Exponential Backoff + Jitter |
| Observabilidade | New Relic (APM/tracing) |
| Dev AWS | LocalStack |
| Testes / CI | Pest (TDD/BDD), GitHub Actions |

**Fora do escopo inicial acordado:** MySQL/Postgres no compose — SQLite só para internals Laravel; negócio no DynamoDB.

---

## O que já foi desenvolvido (bootstrap)

Estado atual: **Fase 4 concluída**. Próximo: Fase 5 (resiliência e observabilidade).

### Infra local

- [`docker-compose.yml`](../docker-compose.yml): Redis, Memcached, LocalStack; serviço `app` (profile `dev`, PHP 8.4).
- [`localstack/init/ready.d/01-init-aws.sh`](../localstack/init/ready.d/01-init-aws.sh): fila `shipsync-jobs`, DLQ `shipsync-jobs-dlq`, bucket `shipsync-local`, tabela `shipsync-records`.
- [`docker/php/Dockerfile`](../docker/php/Dockerfile), [`bin/composer`](../bin/composer), [`bin/test`](../bin/test).
- `.env` do dev alinhado ao [`.env.example`](../.env.example) (Redis, Memcached, `AWS_ENDPOINT`). Dependências PHP via container (`vendor/` no volume).

### Aplicação Laravel

- Laravel **11** + Pest **3**, PHP **≥ 8.4** (`composer.json` / lock).
- [`.env.example`](../.env.example): `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=memcached`, `AWS_ENDPOINT`, recursos LocalStack.
- [`config/services.php`](../config/services.php): bloco `aws` centralizado.
- [`app/Domain/Shipping/`](../app/Domain/Shipping/): VOs (`Cep`, `Money`, `Weight`, `Dimensions`, `Package`), `QuoteRequest`/`Quote`/`QuoteResult`, ports `CarrierQuotePort` e `QuoteRepositoryPort`.
- [`app/Application/Messaging/`](../app/Application/Messaging/): port `MessageQueuePort` + DTO `ReceivedMessage`.
- [`app/Infrastructure/Aws/`](../app/Infrastructure/Aws/): `AwsClientFactory`, `SqsMessageQueue`, `DynamoDbQuoteRepository`, `ShippingQuoteRecordMapper` (SDK + `config('services.aws')`).
- Dependência [`aws/aws-sdk-php`](../composer.json) no `composer.json` — rodar `./bin/composer update` após pull.
- [`app/Providers/AppServiceProvider.php`](../app/Providers/AppServiceProvider.php): bindings dos ports AWS, carrier stub, cache de cotação.
- [`app/Application/Shipping/`](../app/Application/Shipping/): `RequestShippingQuoteUseCase`, `QuoteCachePort`, factory e presenter.
- [`routes/api.php`](../routes/api.php): `POST/GET /api/v1/shipping/quotes` + job [`ProcessShippingQuoteJob`](../app/Jobs/ProcessShippingQuoteJob.php) (fila Redis).
- [`app/Console/Commands/RequestShippingQuoteCommand.php`](../app/Console/Commands/RequestShippingQuoteCommand.php): `shipping:quote` (caso de uso síncrono).
- [`app/Infrastructure/Carriers/StubCarrierQuoteAdapter.php`](../app/Infrastructure/Carriers/StubCarrierQuoteAdapter.php): cotação fake até HTTP real.
- [`config/shipping.php`](../config/shipping.php): TTL do cache Memcached (`SHIPPING_QUOTE_CACHE_TTL`).

### Testes

- [`tests/Infrastructure/LocalServicesHealthTest.php`](../tests/Infrastructure/LocalServicesHealthTest.php): Redis PONG, Memcached STAT, health LocalStack (sqs/s3/dynamodb).
- [`tests/Infrastructure/Aws/`](../tests/Infrastructure/Aws/): round-trip SQS e persistência DynamoDB no LocalStack.
- [`tests/Domain/Shipping/`](../tests/Domain/Shipping/): regras de cotação sem boot Laravel.
- [`tests/Pest.php`](../tests/Pest.php): Laravel boot só em `Feature/`.
- [`tests/Feature/Shipping/ShippingQuoteApiTest.php`](../tests/Feature/Shipping/ShippingQuoteApiTest.php): API + job (fila `sync` em testes).
- [`tests/Unit/Application/Shipping/`](../tests/Unit/Application/Shipping/): caso de uso com doubles.
- Suite verde com infra up + SDK instalado (Domain + Feature + Infrastructure).

### Automação e documentação

- **Skills:** `.cursor/skills/shipsync-{hub,tdd,architecture,aws,concept-docs,desenvolvimento}`.
- **Hooks:** `validate-commit-safe.sh` (commit/add + pedido de commit no chat), lembrete infra Pest, pós-edição TDD/AWS, sync roadmap → skill `shipsync-desenvolvimento`.
- **Conceitos:** [`.cursor/docs/`](../.cursor/docs/README.md) (compose, redis, memcached, localstack, dlq, ports, etc.).
- [`docs/setup.md`](setup.md): dia a dia Docker-first + Pest.

### Ainda não implementado (stack alvo)

- Adapters de transportadoras HTTP (substituir stub).
- Adapter S3 e consumer SQS de longa duração (worker dedicado).
- BullMQ, Lambda local/prod, New Relic, Circuit Breaker, Backoff em código.
- GitHub Actions (CI).

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

### Fase 5 — Resiliência e observabilidade

- [ ] Circuit Breaker + Backoff/Jitter em chamadas HTTP a carriers.
- [ ] New Relic (env, middleware/spans).
- [ ] DLQ: monitoramento e reprocessamento manual documentado.

### Fase 6 — CI/CD e produção

- [ ] GitHub Actions: Pest + lint (Pint) em PR.
- [ ] Pipeline deploy; `AWS_ENDPOINT` vazio em prod.
- [ ] BullMQ (worker Node ou bridge) se ainda necessário para concorrência local.

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

*Última atualização: 2026-09-16.*
