<?php

namespace App\Application\Shipping;

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\Weight;

final class QuoteRequestFactory
{
    /**
     * @param  array{
     *     origin: string,
     *     destination: string,
     *     packages: list<array{
     *         weight_grams: int,
     *         length_cm: int,
     *         width_cm: int,
     *         height_cm: int
     *     }>,
     *     declared_value_cents?: int|null
     * }  $data
     */
    public function fromArray(array $data): QuoteRequest
    {
        $packages = array_map(
            fn (array $package): Package => new Package(
                new Weight((int) $package['weight_grams']),
                new Dimensions(
                    (int) $package['length_cm'],
                    (int) $package['width_cm'],
                    (int) $package['height_cm'],
                ),
            ),
            $data['packages'],
        );

        $declaredValue = isset($data['declared_value_cents'])
            ? new Money((int) $data['declared_value_cents'])
            : null;

        return new QuoteRequest(
            new Cep($data['origin']),
            new Cep($data['destination']),
            $packages,
            $declaredValue,
        );
    }
}
