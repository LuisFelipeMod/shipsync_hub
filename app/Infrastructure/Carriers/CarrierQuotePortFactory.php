<?php

namespace App\Infrastructure\Carriers;

use App\Domain\Shipping\CarrierQuotePort;
use App\Infrastructure\Resilience\CacheCircuitBreakerStateStore;
use App\Infrastructure\Resilience\CircuitBreaker;
use App\Infrastructure\Resilience\ExponentialBackoffWithJitter;
use App\Infrastructure\Resilience\NativeSleeper;
use App\Infrastructure\Resilience\SystemClock;
use Illuminate\Contracts\Cache\Repository;

final class CarrierQuotePortFactory
{
    public function __construct(
        private readonly Repository $cache,
    ) {}

    public function create(): CarrierQuotePort
    {
        $inner = new StubCarrierQuoteAdapter;

        if (! (bool) config('shipping.resilience.enabled', true)) {
            return $inner;
        }

        $resilience = config('shipping.resilience');

        $breaker = new CircuitBreaker(
            key: (string) ($resilience['circuit_key'] ?? 'default-carrier'),
            store: new CacheCircuitBreakerStateStore(
                $this->cache,
                (int) ($resilience['circuit_state_ttl_seconds'] ?? 3600),
            ),
            failureThreshold: (int) ($resilience['circuit_failure_threshold'] ?? 5),
            openSeconds: (int) ($resilience['circuit_open_seconds'] ?? 30),
            clock: new SystemClock,
        );

        $backoff = new ExponentialBackoffWithJitter(
            baseMs: (int) ($resilience['backoff_base_ms'] ?? 100),
            maxMs: (int) ($resilience['backoff_max_ms'] ?? 5_000),
            maxAttempts: (int) ($resilience['max_attempts'] ?? 3),
        );

        return new ResilientCarrierQuoteAdapter(
            inner: $inner,
            circuitBreaker: $breaker,
            backoff: $backoff,
            sleeper: new NativeSleeper,
        );
    }
}
