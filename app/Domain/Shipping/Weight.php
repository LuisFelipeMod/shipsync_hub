<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\InvalidPackage;

final readonly class Weight
{
    public function __construct(private int $grams)
    {
        if ($this->grams < 1) {
            throw new InvalidPackage('Peso deve ser de pelo menos 1 grama.');
        }
    }

    public function grams(): int
    {
        return $this->grams;
    }
}
