---
name: shipsync-desenvolvimento
description: >-
  Mantém docs/desenvolvimento.md atualizado: checkboxes de fases, seção "O que já
  foi desenvolvido", histórico e data. Use quando um objetivo do roadmap for
  concluído, ao marcar [x], após entrega de feature, ou quando o hook
  sync-desenvolvimento-roadmap solicitar.
---

# ShipSync — controle de desenvolvimento

Arquivo canônico: [`docs/desenvolvimento.md`](../../../docs/desenvolvimento.md).

## Quando atualizar

- Checkbox de **Próximas etapas** passou de `[ ]` para `[x]`.
- Entrega fechada (testes verdes + código mergeável) alinhada a um item do roadmap.
- Bootstrap ou stack alvo mudou de forma relevante.

## Checklist de edição (sempre nesta ordem)

1. **Ler** o `desenvolvimento.md` atual por completo.
2. **Marcar** o(s) checkbox(es) da fase correspondente — texto do item intacto.
3. **Seção "O que já foi desenvolvido"** — adicionar bullet curto com link para arquivo/pasta (não duplicar o que já está listado).
4. **Ajustar** a linha `Estado atual:` se a fase geral mudou (ex.: "Fase 2 em andamento").
5. **Histórico resumido** — nova linha na tabela: `| AAAA-MM-DD | Entrega em uma frase |`.
6. **Rodapé** — `*Última atualização: AAAA-MM-DD.*` com a data de hoje.
7. Se **todos** os itens de uma fase estiverem `[x]`, registrar no histórico: `Fase N concluída`.

## O que não fazer

- Não apagar objetivos futuros nem reescrever a visão do produto.
- Não marcar `[x]` sem entrega real (teste/código/doc acordados).
- Não mover roadmap para `.cursor/docs/` — humanos e agentes usam `docs/desenvolvimento.md`.

## Formato dos bullets novos (bootstrap / feito)

```markdown
- [`caminho/relativo`](../caminho/relativo): uma linha do que faz.
```

## Relacionado

- Visão e fases iniciais: [`p-1.md`](../../../p-1.md)
- Conceitos técnicos: [`.cursor/docs/`](../../docs/README.md)
- Co-piloto geral: [shipsync-hub](../shipsync-hub/SKILL.md)
