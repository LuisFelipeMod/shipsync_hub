<?php

use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Exceptions\InvalidQuoteRequest;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use App\Domain\Shipping\Weight;
use App\Infrastructure\Carriers\Exceptions\CarrierTransportException;
use App\Infrastructure\Carriers\ResilientCarrierQuoteAdapter;
use App\Infrastructure\Resilience\CircuitBreaker;
use App\Infrastructure\Resilience\Clock;
use App\Infrastructure\Resilience\ExponentialBackoffWithJitter;
use App\Infrastructure\Resilience\InMemoryCircuitBreakerStateStore;
use App\Infrastructure\Resilience\Sleeper;

describe('ResilientCarrierQuoteAdapter', function () {
    function sampleRequest(): QuoteRequest
    {
        return new QuoteRequest(
            origin: new Cep('01310-100'),
            destination: new Cep('20040-020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
        );
    }

    it('retenta falhas transitórias com backoff antes de desistir', function () {
        $state = (object) ['calls' => 0];
        $inner = new class($state) implements CarrierQuotePort
        {
            public function __construct(private object $state) {}

            public function quote(QuoteRequest $request): QuoteResult
            {
                $this->state->calls++;

                if ($this->state->calls < 3) {
                    throw new CarrierTransportException('timeout');
                }

                return new QuoteResult([
                    new Quote('carrier', 'express', new Money(2500), 2),
                ]);
            }
        };

        $slept = [];
        $sleeper = new class($slept) implements Sleeper
        {
            public function __construct(private array &$slept) {}

            public function sleepMs(int $milliseconds): void
            {
                $this->slept[] = $milliseconds;
            }
        };

        $clock = new class implements Clock
        {
            public function now(): int
            {
                return 100;
            }
        };

        $breaker = new CircuitBreaker(
            key: 'retry-test',
            store: new InMemoryCircuitBreakerStateStore,
            failureThreshold: 5,
            openSeconds: 30,
            clock: $clock,
        );

        $adapter = new ResilientCarrierQuoteAdapter(
            inner: $inner,
            circuitBreaker: $breaker,
            backoff: new ExponentialBackoffWithJitter(10, 100, 3, static fn (): int => 0),
            sleeper: $sleeper,
        );

        $result = $adapter->quote(sampleRequest());

        expect($state->calls)->toBe(3)
            ->and($result->all())->toHaveCount(1)
            ->and($slept)->toBe([10, 20]);
    });

    it('não retenta erros de negócio não transitórios', function () {
        $inner = new class implements CarrierQuotePort
        {
            public function quote(QuoteRequest $request): QuoteResult
            {
                throw new InvalidQuoteRequest('invalid');
            }
        };

        $adapter = new ResilientCarrierQuoteAdapter(
            inner: $inner,
            circuitBreaker: new CircuitBreaker(
                key: 'no-retry',
                store: new InMemoryCircuitBreakerStateStore,
                failureThreshold: 5,
                openSeconds: 30,
                clock: new class implements Clock
                {
                    public function now(): int
                    {
                        return 1;
                    }
                },
            ),
            backoff: new ExponentialBackoffWithJitter(10, 100, 3, static fn (): int => 0),
            sleeper: new class implements Sleeper
            {
                public function sleepMs(int $milliseconds): void {}
            },
        );

        expect(fn () => $adapter->quote(sampleRequest()))->toThrow(InvalidQuoteRequest::class);
    });
});
