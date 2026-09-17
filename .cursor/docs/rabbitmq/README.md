# RabbitMQ

## Por que usamos isso

- **Workers dedicados** com concorrência controlada, complementando Redis/SQS.
- **Management UI** (`:15672`) para inspecionar filas em dev.
- Permite evoluir para integrações AMQP sem acoplar o domínio ao driver Laravel.

## Como aparece no ShipSync

- Serviço [`rabbitmq`](../../../docker-compose.yml) (5672 AMQP, 15672 UI). Credenciais dev: `shipsync` / `shipsync`.
- Conexão Laravel: `QUEUE_CONNECTION=rabbitmq` + bloco em [`config/queue.php`](../../../config/queue.php).
- Pacote: `vladimir-yuldashev/laravel-queue-rabbitmq` (rodar `./bin/composer update` após pull).
- Worker: `php artisan queue:work rabbitmq --queue=shipsync-jobs`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Connection refused | Compose sem `rabbitmq` ou `RABBITMQ_HOST` errado (host vs Docker) |
| Class AMQPLazyConnection not found | Dependências não instaladas — `composer update` |
| Jobs não consomem | Worker não está rodando ou fila/nome diferente de `RABBITMQ_QUEUE` |
