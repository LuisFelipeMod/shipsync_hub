<?php

namespace App\Infrastructure\Testing;

use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;

final class InMemoryQuoteRepository implements QuoteRepositoryPort
{
    /** @var array<string, QuoteResult> */
    private array $items = [];

    public function save(string $id, QuoteRequest $request, QuoteResult $result): void
    {
        $this->items[$id] = $result;
    }

    public function find(string $id): ?QuoteResult
    {
        return $this->items[$id] ?? null;
    }
}
