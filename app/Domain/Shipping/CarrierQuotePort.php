<?php

namespace App\Domain\Shipping;

interface CarrierQuotePort
{
    public function quote(QuoteRequest $request): QuoteResult;
}
