# Ports (contratos de fronteira)

## Por que usamos isso

- O domínio de cotação não pode depender de Correios, Jadlog, DynamoDB ou SQS concretos.
- Testes `tests/Domain` usam fakes que implementam o mesmo contrato — TDD sem Laravel nem AWS.
- Adapters reais (Fase 3+) entram em `app/Infrastructure` sem mudar `QuoteRequest`/`QuoteResult`.

## Como aparece no ShipSync

- [`CarrierQuotePort`](../../../app/Domain/Shipping/CarrierQuotePort.php) — cotação externa.
- [`QuoteRepositoryPort`](../../../app/Domain/Shipping/QuoteRepositoryPort.php) — persistência do resultado.
- Implementações dummy nos testes: [`tests/Domain/Shipping/QuoteResultTest.php`](../../../tests/Domain/Shipping/QuoteResultTest.php).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Domain importa SDK/Guzzle | Falta port — extrair interface e mover HTTP para adapter |
| Teste de domínio sobe LocalStack | Contrato testado com fake, não com infra |
| Port devolve array solto | Tipar `QuoteResult` para o domínio permanecer explícito |
