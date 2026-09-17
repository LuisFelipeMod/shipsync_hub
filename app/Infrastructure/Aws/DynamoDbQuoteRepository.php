<?php

namespace App\Infrastructure\Aws;

use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
final class DynamoDbQuoteRepository implements QuoteRepositoryPort
{
    private DynamoDbAttributeMarshaler $marshaler;

    public function __construct(
        private readonly AwsClientFactory $factory,
        private readonly ShippingQuoteRecordMapper $mapper,
    ) {
        $this->marshaler = new DynamoDbAttributeMarshaler;
    }

    public function save(string $id, QuoteRequest $request, QuoteResult $result): void
    {
        $table = (string) ($this->factory->config()['dynamodb_table'] ?? 'shipsync-records');

        $this->factory->dynamodb()->putItem([
            'TableName' => $table,
            'Item' => $this->marshaler->marshalItem($this->mapper->toItem($id, $request, $result)),
        ]);
    }

    public function find(string $id): ?QuoteResult
    {
        $table = (string) ($this->factory->config()['dynamodb_table'] ?? 'shipsync-records');

        $result = $this->factory->dynamodb()->getItem([
            'TableName' => $table,
            'Key' => $this->marshaler->marshalItem([
                'pk' => ShippingQuoteRecordMapper::PK_PREFIX.'#'.$id,
                'sk' => ShippingQuoteRecordMapper::SK,
            ]),
        ]);

        if (! isset($result['Item'])) {
            return null;
        }

        /** @var array<string, mixed> $item */
        $item = $this->marshaler->unmarshalItem($result['Item']);

        return $this->mapper->toQuoteResult($item);
    }
}
