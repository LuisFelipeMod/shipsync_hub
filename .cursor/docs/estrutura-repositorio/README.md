# Estrutura do repositório (bootstrap)

## Por que usamos isso

- Separar **infra local**, **aplicação Laravel**, **testes** e **automação do Cursor** evita misturar concerns e facilita onboarding.
- O bootstrap atual prova a stack (Redis, Memcached, LocalStack) antes de features de negócio.
- Duas pastas de docs: `.cursor/docs/` (conceitos para o agente) vs `docs/` (setup humano).

## Como aparece no ShipSync

Visão completa para humanos: **[`docs/arquitetura-pastas.md`](../../../docs/arquitetura-pastas.md)**.

```
shipsync_hub/
├── app/Domain/Shipping/     # VOs, QuoteRequest/Result, ports
├── config/services.php      # bloco `aws`
├── docker-compose.yml       # redis, memcached, localstack, app (profile dev)
├── docker/php/Dockerfile
├── localstack/init/ready.d/01-init-aws.sh
├── tests/
│   ├── Pest.php
│   ├── Infrastructure/LocalServicesHealthTest.php
│   ├── Feature/, Unit/
├── bin/composer, bin/test
├── .cursor/skills/*, .cursor/hooks/*
├── docs/setup.md, README.md
└── p-1.md
```

Stack PHP: Laravel **11**, Pest **3**, PHP **≥ 8.4** (lock atual).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Pest “command not found” | `vendor/` ausente — rodar `composer install` |
| Teste infra falha no host | `docker compose up -d` não executado ou portas ocupadas |
| PHP version mismatch | Lock exige 8.4+; imagem `php:8.3-cli` quebra autoload |
| Confusão entre docs | Setup operacional está em `docs/setup.md`, não nesta pasta |
