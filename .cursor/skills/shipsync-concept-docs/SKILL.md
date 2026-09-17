---
name: shipsync-concept-docs
description: >-
  Documentar conceitos e padrões do ShipSync Hub em .cursor/docs/<topico>/README.md
  com 2–3 bullets do porquê e causa raiz de erros. Use ao introduzir Strategy,
  Adapter, Circuit Breaker, DLQ, Lambda, Backoff ou infra nova.
---

# ShipSync — documentação de conceitos (agente)

## Quando criar

Sempre que **introduzir pela primeira vez** (ou de forma relevante) um padrão ou peça de infra listada no prompt do projeto.

## Onde

```
.cursor/docs/
  lambda/README.md
  circuit-breaker/README.md
  dlq/README.md
  strategy/README.md
  ...
```

Nome da pasta: **kebab-case**, curto, em inglês ou português (consistente por pasta).

## Template mínimo (`README.md`)

```markdown
# <Título>

## Por que usamos isso
- ...
- ...
- ...

## Como aparece no ShipSync
[1–2 frases + caminhos de código quando existirem]

## Erros comuns e causa raiz
| Sintoma | Causa raiz |
|---------|------------|
| ... | ... |
```

Manter **conciso** (menos de ~40 linhas por tópico). Atualizar o mesmo arquivo se o conceito evoluir, em vez de duplicar.

## Separação

- `.cursor/docs/` — notas para o **co-piloto** e aprendizado rápido.
- `docs/` na raiz — documentação para **humanos** continuarem o produto (API, setup, ADRs leves).
