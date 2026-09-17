# Controle de desenvolvimento — ShipSync Hub

Documento de **continuidade** para novas sessões (humano ou agente). Atualize este arquivo quando uma etapa relevante for concluída.

Referências: prompt original [`p-1.md`](../p-1.md), setup [`setup.md`](setup.md), conceitos [`.cursor/docs/`](../.cursor/docs/README.md).

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

Estado atual: **fundação pronta**, sem features de negócio (cotação, carriers, consumers SQS).

### Infra local

- [`docker-compose.yml`](../docker-compose.yml): Redis, Memcached, LocalStack; serviço `app` (profile `dev`, PHP 8.4).
- [`localstack/init/ready.d/01-init-aws.sh`](../localstack/init/ready.d/01-init-aws.sh): fila `shipsync-jobs`, DLQ `shipsync-jobs-dlq`, bucket `shipsync-local`, tabela `shipsync-records`.
- [`docker/php/Dockerfile`](../docker/php/Dockerfile), [`bin/composer`](../bin/composer), [`bin/test`](../bin/test).

### Aplicação Laravel

- Laravel **11** + Pest **3**, PHP **≥ 8.4** (`composer.json` / lock).
- [`.env.example`](../.env.example): `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=memcached`, `AWS_ENDPOINT`, recursos LocalStack.
- [`config/services.php`](../config/services.php): bloco `aws` centralizado.
- [`app/Domain/`](../app/Domain/): pasta reservada (`.gitkeep`).

### Testes

- [`tests/Infrastructure/LocalServicesHealthTest.php`](../tests/Infrastructure/LocalServicesHealthTest.php): Redis PONG, Memcached STAT, health LocalStack (sqs/s3/dynamodb).
- [`tests/Pest.php`](../tests/Pest.php): Laravel boot só em `Feature/`.
- Suite verde quando infra está up (5 testes no bootstrap).

### Automação e documentação

- **Skills:** `.cursor/skills/shipsync-{hub,tdd,architecture,aws,concept-docs,desenvolvimento}`.
- **Hooks:** `validate-commit-safe.sh` (commit/add + pedido de commit no chat), lembrete infra Pest, pós-edição TDD/AWS, sync roadmap → skill `shipsync-desenvolvimento`.
- **Conceitos:** [`.cursor/docs/`](../.cursor/docs/README.md) (compose, redis, memcached, localstack, dlq, etc.).
- [`docs/setup.md`](setup.md): Docker, Composer, extensões Fedora.

### Ainda não implementado (stack alvo)

- Casos de uso de frete/cotação, adapters de transportadoras.
- SDK AWS em `app/Infrastructure` (SQS consumer, DynamoDB repository, S3).
- BullMQ, Lambda local/prod, New Relic, Circuit Breaker, Backoff em código.
- GitHub Actions (CI).
- Configuração real de session/cache/queue Redis/Memcached na app (`.env` do dev).

---

## Próximas etapas (ordem sugerida)

Use **TDD**: Pest primeiro, implementação depois. Marque `[x]` aqui ao concluir.

### Fase 1 — Ambiente de dev confiável

- [ ] Garantir `composer install` no host (Fedora: `php-xml`, `php-mbstring`, etc.) ou fluxo `./bin/composer`.
- [ ] `.env` alinhado ao `.env.example` (Redis/Memcached/drivers).
- [ ] `docker compose up -d` documentado no dia a dia; CI local: `./vendor/bin/pest`.

### Fase 2 — Primeiro vertical slice (domínio)

- [ ] Modelar entidades/value objects iniciais em `app/Domain` (ex.: pedido de cotação, resultado).
- [ ] Definir **ports** (interfaces) para cotação externa e persistência.
- [ ] Testes **Unit/Domain** sem Laravel.

### Fase 3 — Infraestrutura AWS (LocalStack)

- [ ] Adapter SQS (publicar/consumir) usando `config('services.aws')`.
- [ ] Repositório DynamoDB para entidade principal.
- [ ] Documentar padrão em `.cursor/docs/` (ex.: `strategy`, `adapter`) ao introduzir.

### Fase 4 — API e jobs

- [ ] Endpoint ou comando artisan fino → caso de uso.
- [ ] Job Laravel + fila Redis; depois migrar/publicar also SQS se fizer sentido arquitetural.
- [ ] Cache Memcached para respostas de cotação (TTL).

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
2. Seguir skills em [`.cursor/skills/shipsync-hub/`](../.cursor/skills/shipsync-hub/SKILL.md).
3. Novo padrão ou infra → `.cursor/docs/<tópico>/README.md` (skill `shipsync-concept-docs`).
4. Objetivo concluído → skill [`shipsync-desenvolvimento`](../.cursor/skills/shipsync-desenvolvimento/SKILL.md) (hook dispara ao marcar `[x]` ou após testes verdes).
5. Não commitar `.env`; hooks bloqueiam tentativas comuns.

---

## Histórico resumido

| Data (aprox.) | Entrega |
|---------------|---------|
| 2026-09-16 | Bootstrap: compose, LocalStack init, teste infra Pest, Laravel+Pest, skills/hooks, docs conceito + setup |

*Última atualização: 2026-09-16.*
