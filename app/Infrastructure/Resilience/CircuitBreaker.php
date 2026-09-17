<?php

namespace App\Infrastructure\Resilience;

use App\Infrastructure\Resilience\Exceptions\CircuitBreakerOpenException;

final class CircuitBreaker
{
    private CircuitBreakerSnapshot $snapshot;

    public function __construct(
        private readonly string $key,
        private readonly CircuitBreakerStateStore $store,
        private readonly int $failureThreshold,
        private readonly int $openSeconds,
        private readonly Clock $clock,
    ) {
        $this->snapshot = $this->store->load($this->key);
    }

    public function guard(): void
    {
        $this->maybeTransitionToHalfOpen();

        if ($this->snapshot->state === CircuitBreakerSnapshot::STATE_OPEN) {
            throw CircuitBreakerOpenException::forKey($this->key);
        }
    }

    public function onSuccess(): void
    {
        $this->snapshot = CircuitBreakerSnapshot::closed();
        $this->persist();
    }

    public function onFailure(): void
    {
        if ($this->snapshot->state === CircuitBreakerSnapshot::STATE_HALF_OPEN) {
            $this->open($this->snapshot->failureCount + 1);

            return;
        }

        $failures = $this->snapshot->failureCount + 1;

        if ($failures >= $this->failureThreshold) {
            $this->open($failures);

            return;
        }

        $this->snapshot = new CircuitBreakerSnapshot(
            CircuitBreakerSnapshot::STATE_CLOSED,
            $failures,
            null,
        );
        $this->persist();
    }

    private function maybeTransitionToHalfOpen(): void
    {
        if ($this->snapshot->state !== CircuitBreakerSnapshot::STATE_OPEN) {
            return;
        }

        $openedAt = $this->snapshot->openedAtEpoch;
        if ($openedAt === null) {
            return;
        }

        if ($this->clock->now() < $openedAt + $this->openSeconds) {
            return;
        }

        $this->snapshot = new CircuitBreakerSnapshot(
            CircuitBreakerSnapshot::STATE_HALF_OPEN,
            $this->snapshot->failureCount,
            null,
        );
        $this->persist();
    }

    private function open(int $failures): void
    {
        $this->snapshot = new CircuitBreakerSnapshot(
            CircuitBreakerSnapshot::STATE_OPEN,
            $failures,
            $this->clock->now(),
        );
        $this->persist();
    }

    private function persist(): void
    {
        $this->store->save($this->key, $this->snapshot);
    }
}
