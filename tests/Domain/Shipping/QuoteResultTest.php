<?php

use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Exceptions\EmptyQuoteResult;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use App\Domain\Shipping\Weight;

describe('Resultado de cotação', function () {
    it('retorna o menor preço entre as ofertas', function () {
        $result = new QuoteResult([
            new Quote('correios', 'SEDEX', new Money(4200), 2),
            new Quote('jadlog', '.Package', new Money(3100), 5),
            new Quote('correios', 'PAC', new Money(1890), 8),
        ]);

        expect($result->cheapest()->carrierId())->toBe('correios')
            ->and($result->cheapest()->service())->toBe('PAC')
            ->and($result->cheapest()->price()->cents())->toBe(1890);
    });

    it('retorna o menor prazo entre transportadoras elegíveis', function () {
        $result = new QuoteResult([
            new Quote('correios', 'PAC', new Money(1890), 8),
            new Quote('jadlog', '.Package', new Money(3100), 5),
            new Quote('correios', 'SEDEX', new Money(4200), 2),
        ]);

        expect($result->fastest()->service())->toBe('SEDEX')
            ->and($result->fastest()->deliveryDays())->toBe(2);
    });

    it('rejeita resultado sem ofertas', function () {
        new QuoteResult([]);
    })->throws(EmptyQuoteResult::class);
});

describe('Ports de cotação e persistência', function () {
    it('o port da transportadora devolve um QuoteResult tipado', function () {
        $request = new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );

        $port = new class implements CarrierQuotePort
        {
            public function quote(QuoteRequest $request): QuoteResult
            {
                return new QuoteResult([
                    new Quote('fake-carrier', 'express', new Money(2500), 3),
                ]);
            }
        };

        $result = $port->quote($request);

        expect($result->all())->toHaveCount(1)
            ->and($result->cheapest()->carrierId())->toBe('fake-carrier');
    });

    it('o port de persistência guarda e recupera o resultado', function () {
        $request = new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );
        $result = new QuoteResult([
            new Quote('fake-carrier', 'express', new Money(2500), 3),
        ]);

        $repository = new class implements QuoteRepositoryPort
        {
            /** @var array<string, QuoteResult> */
            private array $items = [];

            public function save(string $id, QuoteRequest $request, QuoteResult $result): void
            {
                $this->items[$id] = $result;
            }

            public function find(string $id): ?QuoteResult
            {
                return $this->items[$id] ?? null;
            }
        };

        $repository->save('quote-1', $request, $result);

        expect($repository->find('quote-1')?->cheapest()->price()->cents())->toBe(2500)
            ->and($repository->find('missing'))->toBeNull();
    });
});
