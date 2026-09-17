<?php

namespace App\Application\Shipping;

use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteResult;

final class QuoteResultPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(string $id, QuoteResult $result): array
    {
        return [
            'id' => $id,
            'status' => 'ready',
            'quotes' => array_map(fn (Quote $quote): array => $this->quoteToArray($quote), $result->all()),
            'cheapest' => $this->quoteToArray($result->cheapest()),
            'fastest' => $this->quoteToArray($result->fastest()),
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function quoteToArray(Quote $quote): array
    {
        return [
            'carrier_id' => $quote->carrierId(),
            'service' => $quote->service(),
            'price_cents' => $quote->price()->cents(),
            'currency' => $quote->price()->currency(),
            'delivery_days' => $quote->deliveryDays(),
        ];
    }
}
