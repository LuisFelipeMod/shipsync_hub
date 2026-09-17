<?php

namespace App\Infrastructure\Observability;

use App\Application\Observability\TransactionTracerPort;

final class NoOpTransactionTracer implements TransactionTracerPort
{
    public function isEnabled(): bool
    {
        return false;
    }

    public function nameTransaction(string $name): void {}

    public function addAttributes(array $attributes): void {}

    public function traceSegment(string $name, callable $callback)
    {
        return $callback();
    }
}
