---
name: shipsync-aws
description: >-
  AWS no ShipSync Hub via SDK e AWS_ENDPOINT (LocalStack vs produção), SQS com
  DLQ, S3, DynamoDB, Lambda. Use ao configurar filas, storage, persistência ou
  variáveis de ambiente AWS.
---

# ShipSync — AWS e LocalStack

## Regra de ouro

**Nunca** instanciar clientes AWS “hardcoded” para produção sem endpoint configurável. Toda factory/config lê:

- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` (dummy no LocalStack)
- `AWS_DEFAULT_REGION` (ex.: `us-east-1`)
- **`AWS_ENDPOINT`** — vazio ou omitido em prod real; `http://localhost:4566` em dev

Alternar LocalStack ↔ AWS real = **só `.env`**, sem mudar código.

## Recursos locais (init)

Script: [`localstack/init/ready.d/01-init-aws.sh`](../../../localstack/init/ready.d/01-init-aws.sh)

| Recurso | Nome |
|---------|------|
| Fila principal | `shipsync-jobs` |
| DLQ | `shipsync-jobs-dlq` |
| Bucket | `shipsync-local` |
| Tabela DynamoDB | `shipsync-records` (pk/sk) |

## Onde vive no código

- Interfaces de fila/storage/repositório no domínio ou application.
- Implementações em `app/Infrastructure/Aws/` (ou subpastas).
- Config centralizada em `config/services.php` ou provider dedicado — **não** espalhar `env()` fora de config.

## DLQ — por que

Mensagens que falham após N tentativas vão para a DLQ para **não bloquear** a fila principal e permitir análise/reprocessamento.

## Erros comuns (causa raiz)

| Sintoma | Causa raiz provável |
|---------|---------------------|
| Connection refused :4566 | LocalStack não está up (`docker compose up -d`) |
| Queue does not exist | Init não rodou; recriar container LocalStack |
| Signature errors no LocalStack | Credenciais ausentes — usar `test`/`test` ou padrão LocalStack |
| Funciona local, falha em prod | `AWS_ENDPOINT` ainda apontando para LocalStack |
