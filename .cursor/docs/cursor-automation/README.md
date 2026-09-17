# Skills e hooks (Cursor)

## Por que usamos isso

- Manter **TDD, arquitetura, AWS_ENDPOINT e docs de conceito** entre sessões do agente sem repetir o prompt inteiro (`p-1.md`).
- Hooks aplicam checagens **determinísticas** (segredos no git, infra antes de Pest, lembretes pós-edição).

## Como aparece no ShipSync

**Skills** ([`.cursor/skills/`](../../../.cursor/skills/)):

| Skill | Foco |
|-------|------|
| `shipsync-hub` | Visão geral e links |
| `shipsync-tdd` | Ordem teste → código |
| `shipsync-architecture` | Domain, Strategy, Adapter |
| `shipsync-aws` | LocalStack e SDK |
| `shipsync-concept-docs` | Esta pasta `.cursor/docs/` |
| `shipsync-desenvolvimento` | Atualizar `docs/desenvolvimento.md` |
| `commit` | Commit de alterações (PT-BR, autor = git do Luis) |

**Hooks** ([`.cursor/hooks.json`](../../../.cursor/hooks.json)):

| Script | Evento | Efeito |
|--------|--------|--------|
| `validate-commit-safe.sh` | `beforeSubmitPrompt` + `beforeShellExecution` | Valida sensíveis vs `.gitignore` ao pedir commit ou `git add/commit` |
| `remind-infra-before-tests.sh` | `beforeShellExecution` | Pergunta se LocalStack down ao rodar Pest infra |
| `shipsync-post-edit.sh` | `postToolUse` | Contexto: TDD, Domain sem AWS, docs de padrão |
| `sync-desenvolvimento-roadmap.sh` | `postToolUse` | Aciona skill `shipsync-desenvolvimento` ([x], marcos, Pest verde) |

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Hook não dispara | Cursor não recarregou `hooks.json` — reiniciar IDE |
| Script não executável | Falta `chmod +x` em `.cursor/hooks/*.sh` |
| Skill ignorada | Descrição não bate com o pedido — citar “ShipSync” ou skill por nome |
| Docs duplicadas | Conceito em `docs/` e `.cursor/docs/` — usar `.cursor/docs` só para aprendizado agente |
