# LocalStack (AWS em dev)

## Por que usamos isso

- Desenvolver contra **SQS, S3 e DynamoDB** sem conta AWS e sem custo, com o mesmo SDK de produção.
- `AWS_ENDPOINT` aponta para LocalStack; em prod o endpoint some e o SDK usa AWS real.
- Init automático garante que filas e tabelas existam antes dos testes e da app.

## Como aparece no ShipSync

- Container: `localstack` em [`docker-compose.yml`](../../../docker-compose.yml), porta **4566**.
- Init: [`localstack/init/ready.d/01-init-aws.sh`](../../../localstack/init/ready.d/01-init-aws.sh)
  - Fila `shipsync-jobs` + DLQ `shipsync-jobs-dlq`
  - Bucket `shipsync-local`
  - Tabela DynamoDB `shipsync-records` (pk/sk)
- Health: `GET /_localstack/health` — testado em Infrastructure Pest.
- `.env.example`: `AWS_ENDPOINT=http://localhost:4566`, credenciais dummy `test`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Queue does not exist | Init não rodou; recriar volume/container LocalStack |
| Connection refused :4566 | Serviço não está up ou firewall |
| S3 404 no bucket | Bucket não criado — ver logs do init |
| Funciona local, falha em prod | `AWS_ENDPOINT` ainda setado para LocalStack no `.env` de prod |
