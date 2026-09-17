<?php

namespace App\Infrastructure\Aws;

use App\Domain\Shipping\Money;
use App\Domain\Shipping\Package;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use JsonException;

final class ShippingQuoteRecordMapper
{
    public const PK_PREFIX = 'QUOTE';

    public const SK = 'RESULT';

    /**
     * @return array<string, mixed>
     */
    public function toItem(string $id, QuoteRequest $request, QuoteResult $result): array
    {
        return [
            'pk' => self::PK_PREFIX.'#'.$id,
            'sk' => self::SK,
            'payload' => $this->encodePayload($request, $result),
        ];
    }

    public function toQuoteResult(array $item): QuoteResult
    {
        $payload = json_decode($item['payload'], true, flags: JSON_THROW_ON_ERROR);

        $quotes = array_map(
            fn (array $quote): Quote => new Quote(
                (string) $quote['carrier_id'],
                (string) $quote['service'],
                new Money((int) $quote['price_cents'], (string) ($quote['currency'] ?? 'BRL')),
                (int) $quote['delivery_days'],
            ),
            $payload['quotes'],
        );

        return new QuoteResult($quotes);
    }

    private function encodePayload(QuoteRequest $request, QuoteResult $result): string
    {
        $packages = array_map(
            fn (Package $package): array => [
                'weight_grams' => $package->weight()->grams(),
                'length_cm' => $package->dimensions()->lengthCm(),
                'width_cm' => $package->dimensions()->widthCm(),
                'height_cm' => $package->dimensions()->heightCm(),
            ],
            $request->packages(),
        );

        $quotes = array_map(
            fn (Quote $quote): array => [
                'carrier_id' => $quote->carrierId(),
                'service' => $quote->service(),
                'price_cents' => $quote->price()->cents(),
                'currency' => $quote->price()->currency(),
                'delivery_days' => $quote->deliveryDays(),
            ],
            $result->all(),
        );

        $payload = [
            'request' => [
                'origin' => $request->origin()->digits(),
                'destination' => $request->destination()->digits(),
                'packages' => $packages,
                'declared_value_cents' => $request->declaredValue()?->cents(),
            ],
            'quotes' => $quotes,
        ];

        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new JsonException('Falha ao serializar cotação para DynamoDB.', previous: $exception);
        }
    }
}
