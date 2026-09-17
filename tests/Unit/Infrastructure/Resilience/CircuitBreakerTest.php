<?php

use App\Infrastructure\Resilience\CircuitBreaker;
use App\Infrastructure\Resilience\Clock;
use App\Infrastructure\Resilience\Exceptions\CircuitBreakerOpenException;
use App\Infrastructure\Resilience\InMemoryCircuitBreakerStateStore;

describe('CircuitBreaker', function () {
    it('abre após atingir o limite de falhas consecutivas', function () {
        $clock = new class implements Clock
        {
            private int $now = 1_000;

            public function now(): int
            {
                return $this->now;
            }

            public function advance(int $seconds): void
            {
                $this->now += $seconds;
            }
        };

        $breaker = new CircuitBreaker(
            key: 'carrier-test',
            store: new InMemoryCircuitBreakerStateStore,
            failureThreshold: 2,
            openSeconds: 30,
            clock: $clock,
        );

        $breaker->guard();
        $breaker->onFailure();
        $breaker->guard();
        $breaker->onFailure();

        expect(fn () => $breaker->guard())->toThrow(CircuitBreakerOpenException::class);
    });

    it('passa para half-open após o cooldown e fecha em sucesso', function () {
        $clock = new class implements Clock
        {
            private int $now = 5_000;

            public function now(): int
            {
                return $this->now;
            }

            public function advance(int $seconds): void
            {
                $this->now += $seconds;
            }
        };

        $store = new InMemoryCircuitBreakerStateStore;
        $breaker = new CircuitBreaker(
            key: 'carrier-half-open',
            store: $store,
            failureThreshold: 1,
            openSeconds: 60,
            clock: $clock,
        );

        $breaker->guard();
        $breaker->onFailure();
        expect(fn () => $breaker->guard())->toThrow(CircuitBreakerOpenException::class);

        $clock->advance(60);

        $breaker->guard();
        $breaker->onSuccess();

        $breaker->guard();
        expect(true)->toBeTrue();
    });
});
