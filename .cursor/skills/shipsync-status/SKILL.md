---
name: shipsync-status
description: >-
  Resume o status do ShipSync Hub e as próximas etapas a partir de
  docs/desenvolvimento.md. Use quando o usuário pedir status, progresso,
  roadmap, onde paramos, próximas etapas, resumo do projeto ou /status.
disable-model-invocation: true
---

# ShipSync — status do desenvolvimento

## Antes de responder

1. Ler **[`docs/desenvolvimento.md`](../../../docs/desenvolvimento.md)** (fonte canônica).
2. Se necessário, conferir checkboxes das fases e seção **O que já foi desenvolvido**.
3. Não inventar entregas: só citar o que está documentado ou no código verificado na sessão.

## Formato da resposta (PT-BR)

Responder de forma **curta e escaneável**:

```markdown
## Objetivo (1 frase)
...

## Onde estamos
- Fase atual: ...
- Estado: ...

## Já entregue (highlights)
- ...
- ...

## Próximas etapas (ordem sugerida)
1. ...
2. ...
3. ...

## Bloqueios / dependências (se houver)
- ...

## Referência
[docs/desenvolvimento.md](docs/desenvolvimento.md)
```

Regras:

- **Próximas etapas** = itens ainda `[ ]` em *Próximas etapas*, na ordem das fases (1 → 6).
- Se uma fase estiver 100% `[x]`, dizer “Fase N concluída” e apontar a seguinte.
- Mencionar **TDD** e leitura de `docs/desenvolvimento.md` em novas sessões só se relevante ao pedido.
- Não listar todo o bootstrap item a item — 4–7 bullets no máximo em “Já entregue”.

## Atualizar o doc?

Se o status real divergir claramente do markdown (trabalho feito na sessão sem doc), sugerir aplicar a skill [`shipsync-desenvolvimento`](../shipsync-desenvolvimento/SKILL.md) — **não** editar o roadmap sem confirmação do usuário.

## Relacionado

- [`shipsync-hub`](../shipsync-hub/SKILL.md)
- [`docs/setup.md`](../../../docs/setup.md) — setup operacional (não confundir com status de produto)
