<?php

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Exceptions\InvalidQuoteRequest;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\Weight;

describe('Pedido de cotação', function () {
    it('aceita origem, destino distintos e ao menos um pacote', function () {
        $request = new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
            declaredValue: new Money(cents: 15000),
        );

        expect($request->origin()->digits())->toBe('01310100')
            ->and($request->destination()->digits())->toBe('20040020')
            ->and($request->packages())->toHaveCount(1)
            ->and($request->declaredValue()?->cents())->toBe(15000);
    });

    it('rejeita lista vazia de pacotes', function () {
        new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [],
        );
    })->throws(InvalidQuoteRequest::class);

    it('rejeita origem igual ao destino', function () {
        $cep = new Cep('01310-100');

        new QuoteRequest(
            origin: $cep,
            destination: new Cep('01310100'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );
    })->throws(InvalidQuoteRequest::class);

    it('rejeita valor declarado negativo', function () {
        new Money(cents: -1);
    })->throws(InvalidQuoteRequest::class);
});
