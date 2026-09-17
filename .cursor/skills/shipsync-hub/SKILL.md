---
name: shipsync-hub
description: >-
  Co-piloto do ShipSync Hub (gateway de logística e fretes): Laravel 11+, Clean
  Architecture, Redis/Memcached, LocalStack (SQS, S3, DynamoDB), Pest TDD, AWS via
  AWS_ENDPOINT. Use em qualquer tarefa neste repositório ou quando o usuário
  mencionar ShipSync, fretes, cotações ou integrações de transportadoras.
---

# ShipSync Hub — co-piloto

**Status e roadmap (novas sessões):** leia [`docs/desenvolvimento.md`](../../../docs/desenvolvimento.md) antes de implementar features.

Objetivo: entregar rápido **com aprendizado** — explicar o *porquê* dos padrões, não só o *como*.

## Stack (referência rápida)

| Área | Escolha |
|------|---------|
| Back-end | PHP 8.3+, Laravel 11+, SOLID |
| Domínio | `app/Domain` — regras de negócio isoladas |
| Filas / sessões | Redis (`QUEUE_CONNECTION`, `SESSION_DRIVER`) |
| Cache de cotações | Memcached (`CACHE_STORE=memcached`) |
| Persistência de negócio | DynamoDB (LocalStack em dev) |
| Mensageria | SQS + DLQ |
| Arquivos | S3 |
| Testes | Pest (TDD/BDD) |
| AWS em dev | `AWS_ENDPOINT=http://localhost:4566` |

Infra local: [`docker-compose.yml`](../../../docker-compose.yml).

## Regras obrigatórias

1. **TDD primeiro** — ver skill [shipsync-tdd](../shipsync-tdd/SKILL.md).
2. **Domínio desacoplado** — ver skill [shipsync-architecture](../shipsync-architecture/SKILL.md).
3. **AWS só via SDK + `AWS_ENDPOINT`** — ver skill [shipsync-aws](../shipsync-aws/SKILL.md).
4. **Conceitos novos** — documentar em `.cursor/docs/<topico>/README.md` (2–3 tópicos “por que usamos” + causa raiz de erros comuns). Ver skill [shipsync-concept-docs](../shipsync-concept-docs/SKILL.md).
5. **Revisão de código do usuário** — SOLID, gargalos, concorrência/performance.
6. **Docs humanas** — objetivas em `docs/` quando fizer sentido para continuidade do projeto (não duplicar `.cursor/docs`).

## Idioma

Responder em **português** salvo pedido contrário.

## Skills relacionadas

- [shipsync-tdd](../shipsync-tdd/SKILL.md)
- [shipsync-architecture](../shipsync-architecture/SKILL.md)
- [shipsync-aws](../shipsync-aws/SKILL.md)
- [shipsync-concept-docs](../shipsync-concept-docs/SKILL.md)
- [shipsync-desenvolvimento](../shipsync-desenvolvimento/SKILL.md) — roadmap em `docs/desenvolvimento.md`
- [commit](../commit/SKILL.md) — commits em PT-BR com identidade git do Luis
