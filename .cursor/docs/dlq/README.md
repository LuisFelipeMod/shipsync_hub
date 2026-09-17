# DLQ (Dead Letter Queue)

## Por que usamos isso

- Mensagens que **falham repetidamente** não ficam em loop infinito na fila principal.
- Permite **inspecionar e reprocessar** payloads problemáticos sem bloquear jobs saudáveis.
- Padrão AWS SQS: fila principal com **RedrivePolicy** apontando para a DLQ.

## Como aparece no ShipSync

- Criada no init LocalStack: [`01-init-aws.sh`](../../../localstack/init/ready.d/01-init-aws.sh)
  - DLQ: `shipsync-jobs-dlq`
  - Fila: `shipsync-jobs` com `maxReceiveCount=3`
- `.env.example`: `SQS_QUEUE`, `SQS_DLQ`
- Adapters: [`SqsMessageQueue`](../../../app/Infrastructure/Aws/SqsMessageQueue.php) (`main` / `deadLetter`), serviço [`SqsDeadLetterQueueService`](../../../app/Infrastructure/Aws/SqsDeadLetterQueueService.php)
- Comandos Artisan:
  - `php artisan shipsync:dlq:monitor` — contagem aproximada; `--peek=N` lista corpo (sem remover)
  - `php artisan shipsync:dlq:reprocess --limit=10` — republica na fila principal e remove da DLQ

Operação manual documentada em [`docs/setup.md`](../../../docs/setup.md).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Mensagens somem | Foram para a DLQ após N falhas — inspecionar fila DLQ |
| Nada na DLQ | Consumer não está fazendo receive ou erro não dispara retry |
| Redrive não funciona | Policy mal formada no create-queue (init) |
| DLQ enche em dev | Bug no handler; corrigir código antes de reprocessar em massa |
| Reprocess duplicou job | Mensagem ainda na fila principal — reprocess só após corrigir causa |
