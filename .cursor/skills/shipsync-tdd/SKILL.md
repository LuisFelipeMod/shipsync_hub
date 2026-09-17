---
name: shipsync-tdd
description: >-
  TDD com Pest no ShipSync Hub: teste antes da implementação, pastas Feature vs
  Infrastructure, filtros pest. Use ao criar funcionalidades, endpoints, jobs,
  integrações ou quando o usuário pedir testes.
---

# ShipSync — TDD com Pest

## Ordem de trabalho

1. Descrever o comportamento esperado em linguagem de negócio.
2. Escrever o teste Pest **com asserções concretas** (não só `expect(true)->toBeTrue()`).
3. Rodar o teste — deve **falhar** por falta de implementação (vermelho).
4. Implementar o mínimo para passar (verde).
5. Refatorar mantendo os testes verdes.

## Onde colocar testes

| Tipo | Pasta | Boot Laravel? |
|------|-------|----------------|
| HTTP, use cases integrados | `tests/Feature` | Sim |
| Regras puras de domínio | `tests/Unit` ou `tests/Domain` | Preferir não bootar |
| Saúde de infra / TCP / LocalStack | `tests/Infrastructure` | Não |

Exemplo existente: [`tests/Infrastructure/LocalServicesHealthTest.php`](../../../tests/Infrastructure/LocalServicesHealthTest.php).

## Comandos

```bash
docker compose up -d
./vendor/bin/pest
./vendor/bin/pest --filter=NomeDoTeste
./vendor/bin/pest tests/Infrastructure/LocalServicesHealthTest.php
```

## Ao propor código para o usuário

- Enviar **primeiro** o arquivo de teste completo.
- Depois a implementação (ou pedir confirmação antes da implementação se o escopo for grande).

## BDD com Pest

Preferir `describe` / `it` com frases legíveis:

```php
describe('Cotação de frete', function () {
    it('retorna o menor prazo entre transportadoras elegíveis', function () {
        // arrange, act, assert
    });
});
```
