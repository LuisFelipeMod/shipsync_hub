<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\InvalidPackage;

final readonly class Dimensions
{
    public const CUBIC_FACTOR = 6000;

    public function __construct(
        private int $lengthCm,
        private int $widthCm,
        private int $heightCm,
    ) {
        if ($this->lengthCm < 1 || $this->widthCm < 1 || $this->heightCm < 1) {
            throw new InvalidPackage('Cada dimensão deve ter pelo menos 1 cm.');
        }
    }

    public function lengthCm(): int
    {
        return $this->lengthCm;
    }

    public function widthCm(): int
    {
        return $this->widthCm;
    }

    public function heightCm(): int
    {
        return $this->heightCm;
    }

    public function volumetricWeightGrams(): int
    {
        $cubicCm = $this->lengthCm * $this->widthCm * $this->heightCm;

        return (int) ceil(($cubicCm / self::CUBIC_FACTOR) * 1000);
    }
}
