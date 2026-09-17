<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\InvalidQuoteRequest;

final readonly class Money
{
    public function __construct(
        private int $cents,
        private string $currency = 'BRL',
    ) {
        if ($this->cents < 0) {
            throw new InvalidQuoteRequest('Valor monetário não pode ser negativo.');
        }

        if ($this->currency === '') {
            throw new InvalidQuoteRequest('Moeda é obrigatória.');
        }
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function lessThan(self $other): bool
    {
        return $this->cents < $other->cents;
    }
}
