<?php

namespace App\Application\Observability;

interface TransactionTracerPort
{
    public function isEnabled(): bool;

    public function nameTransaction(string $name): void;

    /**
     * @param  array<string, bool|float|int|string|null>  $attributes
     */
    public function addAttributes(array $attributes): void;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function traceSegment(string $name, callable $callback);
}
