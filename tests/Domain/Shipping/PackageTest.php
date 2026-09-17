<?php

use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Exceptions\InvalidPackage;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Weight;

describe('Pacote', function () {
    it('usa o maior entre peso real e peso cubado como peso tarifável', function () {
        $package = new Package(
            weight: new Weight(grams: 500),
            dimensions: new Dimensions(lengthCm: 40, widthCm: 30, heightCm: 20),
        );

        expect($package->billableWeight()->grams())->toBe(4000);
    });

    it('mantém o peso real quando ele supera o cubado', function () {
        $package = new Package(
            weight: new Weight(grams: 8000),
            dimensions: new Dimensions(lengthCm: 10, widthCm: 10, heightCm: 10),
        );

        expect($package->billableWeight()->grams())->toBe(8000);
    });

    it('rejeita peso zero ou negativo', function () {
        new Weight(grams: 0);
    })->throws(InvalidPackage::class);

    it('rejeita dimensão menor que 1 cm', function () {
        new Dimensions(lengthCm: 0, widthCm: 10, heightCm: 10);
    })->throws(InvalidPackage::class);
});
