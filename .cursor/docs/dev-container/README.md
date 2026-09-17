# Container PHP de dev (`app`)

## Por que usamos isso

- Permite **Composer e Pest** sem instalar PHP 8.4 no Fedora host.
- Mesma rede `shipsync` que Redis/Memcached/LocalStack — hosts por **nome de serviço**.
- Profile `dev` evita subir PHP quando só a infra é necessária.

## Como aparece no ShipSync

- Imagem: [`docker/php/Dockerfile`](../../../docker/php/Dockerfile) (PHP 8.4-cli, zip, redis ext, Composer).
- Serviço `app` em [`docker-compose.yml`](../../../docker-compose.yml), profile `dev`.
- Atalhos: [`bin/composer`](../../../bin/composer), [`bin/test`](../../../bin/test) (`--in-container` usa hosts Docker).

Build: `docker compose --profile dev build app`.  
Exemplo: `docker compose --profile dev run --rm app composer install`.

Alternativa documentada em [`docs/setup.md`](../../../docs/setup.md): `docker run … php:8.4-cli ./vendor/bin/pest` com `--network host`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Service app not found | Falta `--profile dev` |
| Pest OK no host, falha no container | Usar `REDIS_HOST=redis`, `AWS_ENDPOINT=http://localstack:4566` |
| Build lento | Primeira vez baixa base `php:8.4-cli-bookworm` |
| Permissão em `vendor/` | UID host vs container — ajustar ownership se necessário |
