<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\InvalidCep;

final readonly class Cep
{
    private string $digits;

    public function __construct(string $value)
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) !== 8 || $digits === '00000000') {
            throw new InvalidCep('CEP deve ter 8 dígitos válidos.');
        }

        $this->digits = $digits;
    }

    public function digits(): string
    {
        return $this->digits;
    }

    public function formatted(): string
    {
        return substr($this->digits, 0, 5).'-'.substr($this->digits, 5);
    }

    public function equals(self $other): bool
    {
        return $this->digits === $other->digits;
    }
}
