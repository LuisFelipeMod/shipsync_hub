# Pest e TDD (bootstrap)

## Por que usamos isso

- **Teste antes da implementação** fixa o comportamento esperado (regra #1 do `p-1.md`).
- Pest com sintaxe `describe`/`it` legível para BDD.
- Suite **Infrastructure** valida dependências externas **sem** bootar Laravel (mais rápido e honesto).

## Como aparece no ShipSync

| Arquivo | Papel |
|---------|--------|
| [`tests/Pest.php`](../../../tests/Pest.php) | `TestCase` só em `Feature/` |
| [`tests/Domain/Shipping/`](../../../tests/Domain/Shipping/) | VOs, pedido/resultado de cotação e ports (sem Laravel) |
| [`tests/Infrastructure/LocalServicesHealthTest.php`](../../../tests/Infrastructure/LocalServicesHealthTest.php) | Redis, Memcached, LocalStack health |
| [`phpunit.xml`](../../../phpunit.xml) | Suite `Infrastructure` + env de hosts/portas |
| [`composer.json`](../../../composer.json) | Script `composer test` → Pest |

Comandos: `./vendor/bin/pest`, `--filter=LocalServices`, [`bin/test`](../../../bin/test) (sobe compose + opção `--in-container`).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Infrastructure lento ou flaky | Infra não healthy; rodar `docker compose up -d` |
| Feature test quebra após mudança | Falta boot/cache — usar `php artisan test` com `.env` testing |
| Pest não encontrado | `vendor/` não instalado |
| `putenv` vs phpunit.xml | Preferir `<env>` no phpunit.xml para hosts de teste |
