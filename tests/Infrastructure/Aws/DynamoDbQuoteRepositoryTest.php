<?php

use App\Domain\Shipping\Cep;
use App\Domain\Shipping\Dimensions;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use App\Domain\Shipping\Weight;
use App\Infrastructure\Aws\AwsClientFactory;
use App\Infrastructure\Aws\DynamoDbQuoteRepository;
use App\Infrastructure\Aws\ShippingQuoteRecordMapper;

describe('Repositório DynamoDB de cotações (LocalStack)', function () {
    beforeEach(function () {
        $factory = AwsClientFactory::fromEnvironment();
        $this->repository = new DynamoDbQuoteRepository($factory, new ShippingQuoteRecordMapper);
    });

    it('persiste e recupera um QuoteResult pelo id', function () {
        $id = 'test-'.uniqid('', true);
        $request = new QuoteRequest(
            origin: new Cep('01310100'),
            destination: new Cep('20040020'),
            packages: [new Package(new Weight(1000), new Dimensions(20, 15, 10))],
            declaredValue: new Money(15000),
        );
        $result = new QuoteResult([
            new Quote('correios', 'PAC', new Money(1890), 8),
            new Quote('jadlog', '.Package', new Money(3100), 5),
        ]);

        $this->repository->save($id, $request, $result);

        $loaded = $this->repository->find($id);

        expect($loaded)->not->toBeNull()
            ->and($loaded->all())->toHaveCount(2)
            ->and($loaded->cheapest()->service())->toBe('PAC')
            ->and($loaded->fastest()->deliveryDays())->toBe(5);
    });

    it('retorna null quando o id não existe', function () {
        expect($this->repository->find('missing-'.uniqid('', true)))->toBeNull();
    });

    it('implementa o port de persistência do domínio', function () {
        expect($this->repository)->toBeInstanceOf(QuoteRepositoryPort::class);
    });
});
