# Notas de conceitos — ShipSync Hub

Documentação para o **co-piloto** e aprendizado rápido.

**Continuidade entre sessões:** [`docs/desenvolvimento.md`](../../docs/desenvolvimento.md) (objetivo, feito, próximas etapas).

Setup operacional: [`docs/setup.md`](../../docs/setup.md).

## Mapa do repositório (bootstrap atual)

| Caminho | Papel |
|---------|--------|
| [`app/Domain/Shipping/`](../../app/Domain/Shipping/) | Regras de cotação (VOs, ports) |
| [`app/Infrastructure/Aws/`](../../app/Infrastructure/Aws/) | Adapters SQS e DynamoDB (LocalStack) |
| [`app/Http/`, `app/Models/`](../../app/) | Camada Laravel padrão (finos; domínio cresce em `Domain/`) |
| [`config/services.php`](../../config/services.php) | Config AWS centralizada (`AWS_ENDPOINT`, bucket, fila, DynamoDB) |
| [`docker-compose.yml`](../../docker-compose.yml) | Redis, Memcached, LocalStack, serviço `app` (profile `dev`) |
| [`docker/php/Dockerfile`](../../docker/php/Dockerfile) | PHP 8.4 CLI + Composer + extensões para dev container |
| [`localstack/init/ready.d/`](../../localstack/init/ready.d/) | Provisionamento SQS/DLQ, S3, DynamoDB ao subir LocalStack |
| [`tests/Domain/`](../../tests/Domain/) | Pest sem Laravel (regras de cotação) |
| [`tests/Infrastructure/`](../../tests/Infrastructure/) | Pest sem boot Laravel (saúde da infra) |
| [`tests/Feature/`, `tests/Unit/`](../../tests/) | Pest com Laravel |
| [`tests/Pest.php`](../../tests/Pest.php) | Quem boota o framework |
| [`bin/composer`](../../bin/composer), [`bin/test`](../../bin/test) | Atalhos via Docker |
| [`.cursor/skills/`](../../.cursor/skills/) | Regras persistentes do agente |
| [`.cursor/hooks/`](../../.cursor/hooks/) | Automação (segredos, infra, pós-edição) |
| [`p-1.md`](../../p-1.md) | Prompt inicial do projeto |

## Tópicos (detalhe)

| Pasta | Assunto |
|-------|---------|
| [estrutura-repositorio](estrutura-repositorio/README.md) | Visão em camadas do que foi gerado |
| [docker-compose](docker-compose/README.md) | Orquestração local |
| [redis](redis/README.md) | Sessions e filas |
| [memcached](memcached/README.md) | Cache de cotações |
| [localstack](localstack/README.md) | AWS simulada em dev |
| [dlq](dlq/README.md) | Dead Letter Queue |
| [aws-endpoint](aws-endpoint/README.md) | Alternar LocalStack ↔ AWS real |
| [pest-tdd](pest-tdd/README.md) | Testes e suite Infrastructure |
| [clean-architecture](clean-architecture/README.md) | Domínio e config |
| [ports](ports/README.md) | Contratos de cotação e persistência |
| [adapter](adapter/README.md) | Adapters AWS (SQS, DynamoDB) |
| [cursor-automation](cursor-automation/README.md) | Skills e hooks |
| [dev-container](dev-container/README.md) | Serviço `app`, `bin/composer`, `bin/test` |
| [projeto](projeto/README.md) | Objetivo e status → `docs/desenvolvimento.md` |

Template: skill [`shipsync-concept-docs`](../skills/shipsync-concept-docs/SKILL.md).
