# Memcached (cache de cotações)

## O que é Memcached

**Memcached** é um servidor de **cache em memória RAM**, pensado para guardar pares **chave → valor** de forma simples e muito rápida. Não é banco de dados: não há consultas SQL, transações nem garantia de persistência — se o processo reinicia ou a memória enche, entradas antigas podem **sumir** (eviction) ou expirar por **TTL** (time to live).

Características que importam no dia a dia:

| Ideia | Significado prático |
|-------|---------------------|
| **Key-value** | Você grava um blob (string serializada, JSON, etc.) sob uma chave única. |
| **Volátil** | Perder cache não quebra o sistema; no máximo você recalcula ou chama a transportadora de novo. |
| **Compartilhado** | Vários processos PHP (workers, requests HTTP) leem o mesmo cache na porta **11211**. |
| **LRU / limite de RAM** | Com `-m 64` no compose, só cabe ~64 MB; entradas menos usadas saem primeiro. |

No Laravel, o driver `memcached` em [`config/cache.php`](../../../config/cache.php) fala com esse servidor via extensão PHP `memcached`. No ShipSync, o adapter [`LaravelQuoteCache`](../../../app/Infrastructure/Cache/LaravelQuoteCache.php) usa o cache do framework para **reutilizar cotações** com a mesma origem, destino e pacotes (chave = hash SHA-256 do pedido), com TTL em [`SHIPPING_QUOTE_CACHE_TTL`](../../../config/shipping.php) (padrão 3600 s).

### Memcached vs Redis neste projeto

| | **Memcached** | **Redis** (no ShipSync) |
|---|----------------|-------------------------|
| **Papel** | Cache de **cotações** (`CACHE_STORE=memcached`) | **Sessão** web e **filas** Laravel (`SESSION_DRIVER`, `QUEUE_CONNECTION`) |
| **Por que separar** | TTL e política de eviction diferentes; cotação pode expirar sem afetar jobs na fila | Filas precisam de `noeviction` para não descartar jobs sob pressão de memória |
| **Modelo mental** | “Acelera leitura repetida do mesmo frete” | “Estado de usuário e trabalho assíncrono” |

---

## Por que usamos isso

- Cache **volátil e rápido** para cotações de frete — dados que podem expirar sem impacto crítico.
- Separa cache de cotação de sessão/fila (Redis), evitando misturar TTLs e políticas diferentes.
- Limite `-m 64` no compose mantém footprint leve em dev.

## Como aparece no ShipSync

- Container: serviço `memcached` em [`docker-compose.yml`](../../../docker-compose.yml).
- `.env.example`: `CACHE_STORE=memcached`, `MEMCACHED_HOST=127.0.0.1`, `SHIPPING_QUOTE_CACHE_TTL=3600`.
- Aplicação: `QuoteCachePort` → [`LaravelQuoteCache`](../../../app/Infrastructure/Cache/LaravelQuoteCache.php) no [`AppServiceProvider`](../../../app/Providers/AppServiceProvider.php).
- Teste infra: protocolo `stats` → resposta contém `STAT` em [`LocalServicesHealthTest.php`](../../../tests/Infrastructure/LocalServicesHealthTest.php).
- No container `app` / `web`: `MEMCACHED_HOST=memcached` ([`docker/env/web.env`](../../../docker/env/web.env) e [`DockerEnvironmentOverrides`](../../../app/Infrastructure/Docker/DockerEnvironmentOverrides.php)).

## Erros comuns e causa raiz

| Sintoma | Causa raiz |
|---------|------------|
| Cache sempre miss | `CACHE_STORE` não é `memcached` no `.env` (default Laravel é `database`) |
| Connection refused :11211 | Memcached não subiu no compose |
| Cotações “antigas” demais | TTL alto em `SHIPPING_QUOTE_CACHE_TTL`; ou mesma chave de fingerprint (origem/destino/pacotes iguais) |
| Teste falha no container | Host deve ser `memcached`, não `127.0.0.1` |
| Hit no host, miss no Docker | `.env` do host com `127.0.0.1` ok no Pest local; container precisa override para nome do serviço |
