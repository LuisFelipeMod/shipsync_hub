<?php

namespace App\Domain\Shipping;

interface QuoteRepositoryPort
{
    public function save(string $id, QuoteRequest $request, QuoteResult $result): void;

    public function find(string $id): ?QuoteResult;
}
