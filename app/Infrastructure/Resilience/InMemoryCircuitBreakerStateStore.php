<?php

namespace App\Infrastructure\Resilience;

final class InMemoryCircuitBreakerStateStore implements CircuitBreakerStateStore
{
    /** @var array<string, CircuitBreakerSnapshot> */
    private array $snapshots = [];

    public function load(string $key): CircuitBreakerSnapshot
    {
        return $this->snapshots[$key] ?? CircuitBreakerSnapshot::closed();
    }

    public function save(string $key, CircuitBreakerSnapshot $snapshot): void
    {
        $this->snapshots[$key] = $snapshot;
    }
}
