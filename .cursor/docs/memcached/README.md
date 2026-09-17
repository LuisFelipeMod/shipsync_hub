# Memcached (cache de cotações)

## Por que usamos isso

- Cache **volátil e rápido** para cotações de frete — dados que podem expirar sem impacto crítico.
- Separa cache de cotação de sessão/fila (Redis), evitando misturar TTLs e políticas diferentes.
- Limite `-m 64` no compose mantém footprint leve em dev.

## Como aparece no ShipSync

- Container: serviço `memcached` em [`docker-compose.yml`](../../../docker-compose.yml).
- `.env.example`: `CACHE_STORE=memcached`, `MEMCACHED_HOST=127.0.0.1`.
- Teste infra: protocolo `stats` → resposta contém `STAT` em [`LocalServicesHealthTest.php`](../../../tests/Infrastructure/LocalServicesHealthTest.php).
- No container `app`: `MEMCACHED_HOST=memcached`.

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Cache sempre miss | `CACHE_STORE` não é `memcached` no `.env` |
| Connection refused :11211 | Memcached não subiu no compose |
| Cotações “antigas” demais | TTL/config de cache da aplicação (ainda não implementado) |
| Teste falha no container | Host deve ser `memcached`, não `127.0.0.1` |
