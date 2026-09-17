# New Relic (APM)

## Por que usamos isso

- **Tracing** de requests HTTP e jobs ajuda a achar gargalos (carrier, cache, DynamoDB).
- **Atributos customizados** (`quote.id`, rota) ligam logs de negócio ao APM.
- Em dev/local a extensão PHP costuma estar ausente — o app usa **NoOp** sem quebrar.

## Como aparece no ShipSync

- Config: [`config/newrelic.php`](../../../config/newrelic.php) + variáveis `NEW_RELIC_*` no [`.env.example`](../../../.env.example).
- Port: [`TransactionTracerPort`](../../../app/Application/Observability/TransactionTracerPort.php).
- Implementações: [`NewRelicTransactionTracer`](../../../app/Infrastructure/Observability/NewRelicTransactionTracer.php) / [`NoOpTransactionTracer`](../../../app/Infrastructure/Observability/NoOpTransactionTracer.php).
- Middleware HTTP: [`ObserveHttpRequests`](../../../app/Http/Middleware/ObserveHttpRequests.php).
- Spans: [`RequestShippingQuoteUseCase`](../../../app/Application/Shipping/RequestShippingQuoteUseCase.php) (`shipping.carrier.quote`), job [`ProcessShippingQuoteJob`](../../../app/Jobs/ProcessShippingQuoteJob.php).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Nada no New Relic | `NEW_RELIC_ENABLED=false` ou extensão `newrelic` não instalada no PHP-FPM/CLI de prod |
| Transações genéricas | Middleware desligado ou rota sem nome — conferir `ObserveHttpRequests` |
| Spans duplicados | Agente já instrumenta HTTP; custom segments só onde agrega (carrier, job) |
