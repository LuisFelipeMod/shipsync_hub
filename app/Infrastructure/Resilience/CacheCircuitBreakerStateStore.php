<?php

namespace App\Infrastructure\Resilience;

use Illuminate\Contracts\Cache\Repository;

final class CacheCircuitBreakerStateStore implements CircuitBreakerStateStore
{
    public function __construct(
        private readonly Repository $cache,
        private readonly int $ttlSeconds,
    ) {}

    public function load(string $key): CircuitBreakerSnapshot
    {
        $payload = $this->cache->get($this->cacheKey($key));

        if (! is_array($payload)) {
            return CircuitBreakerSnapshot::closed();
        }

        return new CircuitBreakerSnapshot(
            state: (string) ($payload['state'] ?? CircuitBreakerSnapshot::STATE_CLOSED),
            failureCount: (int) ($payload['failure_count'] ?? 0),
            openedAtEpoch: isset($payload['opened_at']) ? (int) $payload['opened_at'] : null,
        );
    }

    public function save(string $key, CircuitBreakerSnapshot $snapshot): void
    {
        $this->cache->put($this->cacheKey($key), [
            'state' => $snapshot->state,
            'failure_count' => $snapshot->failureCount,
            'opened_at' => $snapshot->openedAtEpoch,
        ], $this->ttlSeconds);
    }

    private function cacheKey(string $key): string
    {
        return 'shipsync:circuit-breaker:'.$key;
    }
}
