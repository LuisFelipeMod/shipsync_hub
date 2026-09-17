---
name: shipsync-architecture
description: >-
  Clean Architecture e SOLID no ShipSync Hub: app/Domain, Strategy, Adapter,
  interfaces para filas e APIs externas. Use ao criar classes PHP, integrações
  de transportadora, jobs ou refatorações.
---

# ShipSync — arquitetura

## Camadas (alvo)

```
app/Domain/          # entidades, value objects, contratos de domínio, exceções
app/Application/     # casos de uso (orquestração), DTOs de entrada/saída
app/Infrastructure/  # adapters: AWS, HTTP carriers, Redis, implementações
```

Regras de negócio **não** importam SDK AWS, Guzzle direto em controllers, nem facades Laravel dentro de `Domain`.

## Padrões previstos

| Padrão | Uso no ShipSync |
|--------|------------------|
| **Strategy** | Algoritmo de cotação / seleção de transportadora |
| **Adapter** | Normalizar API de cada carrier para contrato interno |
| **Circuit Breaker** | Proteger chamadas externas instáveis |
| **Backoff + Jitter** | Retries em filas e HTTP |

Ao introduzir um padrão, documentar em `.cursor/docs/<nome-do-padrao>/README.md` (skill shipsync-concept-docs).

## Interfaces

Tudo que cruza fronteira (nuvem, fila, HTTP externo) expõe **interface** no domínio ou application; implementação em `Infrastructure`.

```php
// app/Domain/Shipping/CarrierQuotePort.php (exemplo)
interface CarrierQuotePort
{
    public function quote(ShipmentRequest $request): QuoteResult;
}
```

## Controllers e jobs

Controllers e jobs Laravel são **finos**: validam entrada, chamam caso de uso, mapeiam resposta.

## Revisão de código (quando o usuário enviar trechos)

Verificar: SRP, dependência de abstrações, acoplamento a AWS/concrete classes, N+1, idempotência em consumers SQS.
