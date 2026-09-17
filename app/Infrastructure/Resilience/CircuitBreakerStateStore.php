<?php

namespace App\Infrastructure\Resilience;

interface CircuitBreakerStateStore
{
    public function load(string $key): CircuitBreakerSnapshot;

    public function save(string $key, CircuitBreakerSnapshot $snapshot): void;
}
