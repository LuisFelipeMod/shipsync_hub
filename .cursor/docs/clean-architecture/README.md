# Clean Architecture (esqueleto)

## Por que usamos isso

- Regras de **cotação, fretes e integrações** não devem depender de Laravel, AWS ou HTTP concretos.
- **SOLID** e ports/adapters facilitam testes unitários e troca de transportadoras (Strategy/Adapter).
- O prompt exige domínio em `app/Domain` e interfaces para fronteiras externas.

## Como aparece no ShipSync

- Domínio inicial: [`app/Domain/Shipping/`](../../../app/Domain/Shipping/) (`QuoteRequest`, `QuoteResult`, VOs, ports).
- Alvo de camadas (skill `shipsync-architecture`):
  - `app/Domain` — entidades, contratos, exceções
  - `app/Application` — casos de uso (a criar)
  - `app/Infrastructure` — AWS, HTTP carriers (a criar)
- Controllers/jobs Laravel permanecem finos em `app/Http`.
- Config de fronteira AWS já centralizada: [`config/services.php`](../../../config/services.php).

Persistência de negócio: **DynamoDB** (não MySQL/Postgres no compose). SQLite só para internals Laravel (`database/database.sqlite`).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Domain usa Facades/AWS SDK | Acoplamento — extrair port + adapter |
| Lógica no Controller | Caso de uso não extraído para Application/Domain |
| Teste unitário lento | Teste subiu stack Laravel desnecessariamente |
| Duas fontes de verdade config | `env()` fora de `config/` — centralizar |
