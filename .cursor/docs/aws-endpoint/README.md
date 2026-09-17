# Abstração AWS_ENDPOINT

## Por que usamos isso

- **Um único código** para dev (LocalStack) e produção (AWS real) — troca só variáveis de ambiente.
- Regra do projeto: nunca acoplar URLs fixas da AWS no domínio ou em adapters espalhados.
- Credenciais dummy (`test`) bastam no LocalStack; prod usa IAM/roles reais.

## Como aparece no ShipSync

- `.env.example`: `AWS_ENDPOINT=http://localhost:4566`, `AWS_USE_PATH_STYLE_ENDPOINT=true`
- Config: [`config/services.php`](../../../config/services.php) → chave `aws` (`endpoint`, `bucket`, `dynamodb_table`, `sqs_queue`)
- Compose `app`: `AWS_ENDPOINT=http://localstack:4566` (hostname interno)
- Teste infra lê `getenv('AWS_ENDPOINT')` com default `http://localhost:4566`

Implementações futuras: factories em `app/Infrastructure/Aws/` lendo `config('services.aws')`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| SDK aponta para AWS real em dev | `AWS_ENDPOINT` vazio ou ausente no `.env` |
| Signature / access denied LocalStack | Credenciais não definidas |
| Path-style S3 falha | `AWS_USE_PATH_STYLE_ENDPOINT` false no LocalStack |
| Domínio importa `Aws\Sqs\SqsClient` | Violação de camada — mover para Infrastructure |
