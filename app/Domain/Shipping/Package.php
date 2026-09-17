<?php

namespace App\Domain\Shipping;

final readonly class Package
{
    public function __construct(
        private Weight $weight,
        private Dimensions $dimensions,
    ) {}

    public function weight(): Weight
    {
        return $this->weight;
    }

    public function dimensions(): Dimensions
    {
        return $this->dimensions;
    }

    public function billableWeight(): Weight
    {
        $grams = max($this->weight->grams(), $this->dimensions->volumetricWeightGrams());

        return new Weight($grams);
    }
}
