# ShipSync Hub — objetivo e status

## Por que usamos isso

- Novas sessões do Cursor **perdem contexto**; um único “north star” evita reexplicar o produto a cada chat.
- Separa **visão** (objetivo), **feito** (bootstrap) e **próximo** (roadmap) do detalhe técnico por tópico.

## Como aparece no ShipSync

Documento canônico (atualizar ao fechar etapas):

**[`docs/desenvolvimento.md`](../../../docs/desenvolvimento.md)**

Resumo em uma linha: gateway de logística/fretes, Laravel + event-driven AWS, TDD, domínio em `app/Domain`.

Prompt original: [`p-1.md`](../../../p-1.md).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Agente implementa feature sem teste | Não leu `desenvolvimento.md` / skill `shipsync-tdd` |
| Escopo inflado numa sessão | Roadmap em fases ignorado — pegar só o próximo checkbox |
| Doc desatualizada | Etapa concluída sem editar `docs/desenvolvimento.md` |
