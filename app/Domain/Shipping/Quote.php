<?php

namespace App\Domain\Shipping;

final readonly class Quote
{
    public function __construct(
        private string $carrierId,
        private string $service,
        private Money $price,
        private int $deliveryDays,
    ) {}

    public function carrierId(): string
    {
        return $this->carrierId;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function deliveryDays(): int
    {
        return $this->deliveryDays;
    }
}
