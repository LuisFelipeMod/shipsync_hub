<?php

namespace App\Infrastructure\Carriers;

use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\QuoteRequest;
use App\Domain\Shipping\QuoteResult;
use App\Infrastructure\Resilience\CircuitBreaker;
use App\Infrastructure\Resilience\ExponentialBackoffWithJitter;
use App\Infrastructure\Resilience\RetryableCarrierFailure;
use App\Infrastructure\Resilience\Sleeper;
use Throwable;

final class ResilientCarrierQuoteAdapter implements CarrierQuotePort
{
    public function __construct(
        private readonly CarrierQuotePort $inner,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly ExponentialBackoffWithJitter $backoff,
        private readonly Sleeper $sleeper,
    ) {}

    public function quote(QuoteRequest $request): QuoteResult
    {
        $this->circuitBreaker->guard();

        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                $result = $this->inner->quote($request);
                $this->circuitBreaker->onSuccess();

                return $result;
            } catch (Throwable $exception) {
                if (! $this->shouldRetry($exception, $attempt)) {
                    $this->circuitBreaker->onFailure();

                    throw $exception;
                }

                $this->sleeper->sleepMs($this->backoff->delayMs($attempt));
            }
        }
    }

    private function shouldRetry(Throwable $exception, int $attempt): bool
    {
        if ($attempt >= $this->backoff->maxAttempts()) {
            return false;
        }

        return $exception instanceof RetryableCarrierFailure;
    }
}
