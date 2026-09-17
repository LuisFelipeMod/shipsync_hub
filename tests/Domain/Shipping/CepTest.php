<?php

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Exceptions\InvalidCep;

describe('CEP', function () {
    it('normaliza hífen e espaços para 8 dígitos', function () {
        $cep = new Cep('01310-100');

        expect($cep->digits())->toBe('01310100')
            ->and($cep->formatted())->toBe('01310-100');
    });

    it('considera iguais CEPs com e sem formatação', function () {
        expect((new Cep('01310100'))->equals(new Cep('01310-100')))->toBeTrue();
    });

    it('rejeita CEP com quantidade inválida de dígitos', function () {
        new Cep('01310');
    })->throws(InvalidCep::class);

    it('rejeita CEP só com zeros', function () {
        new Cep('00000-000');
    })->throws(InvalidCep::class);
});
