# Adapter (AWS e transportadoras)

## Por que usamos isso

- APIs externas (SQS, DynamoDB, HTTP de carriers) têm formatos e SDKs diferentes do domínio.
- O **Adapter** traduz entre o contrato interno (ports) e o protocolo de cada serviço, sem vazar SDK para `app/Domain`.
- Troca LocalStack ↔ AWS real continua só via `.env` / `config/services.php`.

## Como aparece no ShipSync

- Fila: `MessageQueuePort` (`app/Application/Messaging/`) implementado por `SqsMessageQueue` em `app/Infrastructure/Aws/`.
- Persistência de cotação: `QuoteRepositoryPort` implementado por `DynamoDbQuoteRepository` + `ShippingQuoteRecordMapper`.
- Clientes AWS centralizados em `AwsClientFactory` (lê `config('services.aws')`).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Class `Aws\Sqs\SqsClient` not found | `composer install` sem `aws/aws-sdk-php` |
| Queue does not exist | LocalStack init não rodou ou fila renomeada sem atualizar `SQS_QUEUE` |
| Item não encontrado no DynamoDB | `pk`/`sk` divergentes do mapper ou tabela errada (`DYNAMODB_TABLE`) |
| Funciona no Pest, falha no artisan | `.env` do app diferente do `phpunit.xml` (endpoint/credenciais) |
