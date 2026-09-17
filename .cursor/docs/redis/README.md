# Redis (sessions e jobs)

## Por que usamos isso

- **Baixa latência** e estruturas adequadas para filas e sessões web sem banco relacional no compose.
- Alinha com o prompt: `SESSION_DRIVER=redis` e `QUEUE_CONNECTION=redis`.
- `maxmemory-policy noeviction` evita que o Redis descarte chaves de fila sob pressão de memória.

## Como aparece no ShipSync

- Container: serviço `redis` em [`docker-compose.yml`](../../../docker-compose.yml).
- `.env.example`: `REDIS_HOST=127.0.0.1`, `REDIS_PORT=6379`, drivers session/queue em redis.
- Teste: [`tests/Infrastructure/LocalServicesHealthTest.php`](../../../tests/Infrastructure/LocalServicesHealthTest.php) (PING → PONG).
- Dentro do compose `app`: `REDIS_HOST=redis` (nome do serviço, não localhost).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Connection refused :6379 | Container Redis não está up |
| Teste OK no host, falha no `app` | Usar host `redis` dentro da rede Docker |
| Jobs “somem” | Política errada de eviction (aqui usamos `noeviction`) |
| Sessão não persiste | `SESSION_DRIVER` ainda em `database` no `.env` |
