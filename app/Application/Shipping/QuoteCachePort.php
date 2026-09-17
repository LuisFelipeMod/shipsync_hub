<?php

namespace App\Application\Shipping;

use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;

interface QuoteCachePort
{
    public function get(QuoteRequest $request): ?QuoteResult;

    public function put(QuoteRequest $request, QuoteResult $result): void;
}
