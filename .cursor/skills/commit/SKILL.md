---
name: commit
description: >-
  Cria commit git de todas as alterações pendentes com mensagem em PT-BR, usando
  apenas a identidade git configurada no repositório do usuário. Use quando o
  usuário pedir commit, committar, /commit, salvar no git ou versionar alterações.
disable-model-invocation: true
---

# Commit (ShipSync Hub)

## Regras obrigatórias

Faça commit de todas alterações. Nunca faça o commit usando o usuário do agente ou qualquer outro como author ou co-author, sempre use apenas o meu usuário do git. O commit precisa ter uma descrição com um sumário explicando a modificação. PT-BR.

### Identidade git

- **Não** passar `--author`, `--committer`, `-c user.name=`, `-c user.email=`.
- **Não** alterar `git config` (local ou global).
- **Não** adicionar trailer `Co-authored-by:` (Cursor, agente, bot, etc.).
- Deixar o Git usar **somente** `user.name` e `user.email` já configurados na máquina do Luis.

Antes do commit, opcionalmente conferir: `git var GIT_AUTHOR_IDENT` (deve ser o usuário humano, não identidade de agente).

## Fluxo

1. Em paralelo: `git status`, `git diff` (staged + unstaged), `git log -5 --oneline` (estilo de mensagem).
2. **Não** commitar `.env`, chaves, credenciais ou arquivos claramente sensíveis — avisar se estiverem staged. O hook `validate-commit-safe.sh` bloqueia commit se algo sensível estiver fora do `.gitignore` ou indexado indevidamente.
3. `git add` de tudo que pertence à entrega (respeitar `.gitignore`).
4. Mensagem em **português**, formato:

```text
<tipo>: <sumário curto no imperativo>

<1–3 frases: o que mudou e por quê, foco no propósito>
```

Tipos usuais: `feat`, `fix`, `docs`, `chore`, `test`, `refactor`.

5. Commit via HEREDOC:

```bash
git commit -m "$(cat <<'EOF'
feat(escopo): sumário

Corpo explicando a modificação.
EOF
)"
```

6. `git status` após o commit para confirmar sucesso.
7. **Não** fazer `push` salvo pedido explícito.

## Falhas

- Hook de pre-commit falhou → corrigir e **novo** commit (não `--amend` salvo exceções das regras do projeto).
- Nada para commitar → informar; não criar commit vazio.

## Relacionado

- Roadmap após entrega relevante: skill [`shipsync-desenvolvimento`](../shipsync-desenvolvimento/SKILL.md).
