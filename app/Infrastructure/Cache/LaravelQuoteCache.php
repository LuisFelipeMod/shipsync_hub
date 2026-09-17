<?php

namespace App\Infrastructure\Cache;

use App\Application\Shipping\QuoteCachePort;
use App\Domain\Shipping\Money;
use App\Domain\Shipping\Quote;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use JsonException;

final class LaravelQuoteCache implements QuoteCachePort
{
    private const PREFIX = 'shipping_quote:';

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly int $ttlSeconds,
    ) {}

    public function get(QuoteRequest $request): ?QuoteResult
    {
        $payload = $this->cache->get($this->key($request));
        if (! is_array($payload)) {
            return null;
        }

        return $this->hydrate($payload);
    }

    public function put(QuoteRequest $request, QuoteResult $result): void
    {
        $this->cache->put(
            $this->key($request),
            $this->dehydrate($result),
            $this->ttlSeconds,
        );
    }

    private function key(QuoteRequest $request): string
    {
        $packages = array_map(
            fn ($package): array => [
                'weight_grams' => $package->weight()->grams(),
                'length_cm' => $package->dimensions()->lengthCm(),
                'width_cm' => $package->dimensions()->widthCm(),
                'height_cm' => $package->dimensions()->heightCm(),
            ],
            $request->packages(),
        );

        $fingerprint = [
            'origin' => $request->origin()->digits(),
            'destination' => $request->destination()->digits(),
            'packages' => $packages,
            'declared_value_cents' => $request->declaredValue()?->cents(),
        ];

        try {
            $hash = hash('sha256', json_encode($fingerprint, JSON_THROW_ON_ERROR));
        } catch (JsonException $exception) {
            throw new JsonException('Falha ao gerar chave de cache de cotação.', previous: $exception);
        }

        return self::PREFIX.$hash;
    }

    /**
     * @return list<array<string, int|string>>
     */
    private function dehydrate(QuoteResult $result): array
    {
        return array_map(
            fn (Quote $quote): array => [
                'carrier_id' => $quote->carrierId(),
                'service' => $quote->service(),
                'price_cents' => $quote->price()->cents(),
                'currency' => $quote->price()->currency(),
                'delivery_days' => $quote->deliveryDays(),
            ],
            $result->all(),
        );
    }

    /**
     * @param  list<array<string, int|string>>  $payload
     */
    private function hydrate(array $payload): QuoteResult
    {
        $quotes = array_map(
            fn (array $quote): Quote => new Quote(
                (string) $quote['carrier_id'],
                (string) $quote['service'],
                new Money((int) $quote['price_cents'], (string) ($quote['currency'] ?? 'BRL')),
                (int) $quote['delivery_days'],
            ),
            $payload,
        );

        return new QuoteResult($quotes);
    }
}
