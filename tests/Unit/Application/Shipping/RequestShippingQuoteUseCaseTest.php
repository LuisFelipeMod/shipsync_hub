<?php

use App\Application\Shipping\QuoteCachePort;
use App\Infrastructure\Observability\NoOpTransactionTracer;
use App\Application\Shipping\RequestShippingQuoteUseCase;
use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use App\Domain\Shipping\Weight;

describe('RequestShippingQuoteUseCase', function () {
    it('consulta transportadora, persiste e grava cache quando não há hit', function () {
        $request = new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );

        $state = (object) ['carrierCalls' => 0, 'saved' => false, 'cachePut' => false];

        $carrier = new class($state) implements CarrierQuotePort
        {
            public function __construct(private object $state) {}

            public function quote(QuoteRequest $request): QuoteResult
            {
                $this->state->carrierCalls++;

                return new QuoteResult([
                    new Quote('stub', 'express', new Money(2500), 3),
                ]);
            }
        };

        $repository = new class($state) implements QuoteRepositoryPort
        {
            public function __construct(private object $state) {}

            public function save(string $id, QuoteRequest $request, QuoteResult $result): void
            {
                $this->state->saved = $id === 'quote-1';
            }

            public function find(string $id): ?QuoteResult
            {
                return null;
            }
        };

        $cache = new class($state) implements QuoteCachePort
        {
            public function __construct(private object $state) {}

            public function get(QuoteRequest $request): ?QuoteResult
            {
                return null;
            }

            public function put(QuoteRequest $request, QuoteResult $result): void
            {
                $this->state->cachePut = true;
            }
        };

        $useCase = new RequestShippingQuoteUseCase($carrier, $repository, $cache, new NoOpTransactionTracer);
        $result = $useCase->execute('quote-1', $request);

        expect($state->carrierCalls)->toBe(1)
            ->and($state->saved)->toBeTrue()
            ->and($state->cachePut)->toBeTrue()
            ->and($result->cheapest()->carrierId())->toBe('stub');
    });

    it('reutiliza cache e não chama transportadora de novo', function () {
        $request = new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );

        $cached = new QuoteResult([
            new Quote('cached', 'economy', new Money(1500), 7),
        ]);

        $state = (object) ['carrierCalls' => 0];

        $carrier = new class($state) implements CarrierQuotePort
        {
            public function __construct(private object $state) {}

            public function quote(QuoteRequest $request): QuoteResult
            {
                $this->state->carrierCalls++;

                return new QuoteResult([
                    new Quote('stub', 'express', new Money(2500), 3),
                ]);
            }
        };

        $repository = new class implements QuoteRepositoryPort
        {
            public function save(string $id, QuoteRequest $request, QuoteResult $result): void {}

            public function find(string $id): ?QuoteResult
            {
                return null;
            }
        };

        $cache = new class($cached) implements QuoteCachePort
        {
            public function __construct(private QuoteResult $cached) {}

            public function get(QuoteRequest $request): ?QuoteResult
            {
                return $this->cached;
            }

            public function put(QuoteRequest $request, QuoteResult $result): void {}
        };

        $useCase = new RequestShippingQuoteUseCase($carrier, $repository, $cache, new NoOpTransactionTracer);
        $result = $useCase->execute('quote-2', $request);

        expect($state->carrierCalls)->toBe(0)
            ->and($result->cheapest()->carrierId())->toBe('cached');
    });
});
