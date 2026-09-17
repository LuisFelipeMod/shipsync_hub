<?php

namespace App\Infrastructure\Carriers;

use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;

/**
 * Adapter de desenvolvimento até integrações HTTP reais com transportadoras.
 */
final class StubCarrierQuoteAdapter implements CarrierQuotePort
{
    public function quote(QuoteRequest $request): QuoteResult
    {
        return new QuoteResult([
            new Quote('correios', 'PAC', new Money(1890), 8),
            new Quote('jadlog', '.Package', new Money(3100), 5),
            new Quote('correios', 'SEDEX', new Money(4200), 2),
        ]);
    }
}
