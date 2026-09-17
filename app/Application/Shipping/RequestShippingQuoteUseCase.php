<?php

namespace App\Application\Shipping;

use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;

final class RequestShippingQuoteUseCase
{
    public function __construct(
        private readonly CarrierQuotePort $carrier,
        private readonly QuoteRepositoryPort $repository,
        private readonly QuoteCachePort $cache,
    ) {}

    public function execute(string $id, QuoteRequest $request): QuoteResult
    {
        $cached = $this->cache->get($request);
        if ($cached !== null) {
            $this->repository->save($id, $request, $cached);

            return $cached;
        }

        $result = $this->carrier->quote($request);
        $this->cache->put($request, $result);
        $this->repository->save($id, $request, $result);

        return $result;
    }
}
