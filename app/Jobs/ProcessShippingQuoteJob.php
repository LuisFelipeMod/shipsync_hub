<?php

namespace App\Jobs;

use App\Application\Observability\TransactionTracerPort;
use App\Application\Shipping\QuoteRequestFactory;
use App\Application\Shipping\RequestShippingQuoteUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessShippingQuoteJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{
     *     origin: string,
     *     destination: string,
     *     packages: list<array{
     *         weight_grams: int,
     *         length_cm: int,
     *         width_cm: int,
     *         height_cm: int
     *     }>,
     *     declared_value_cents?: int|null
     * }  $payload
     */
    public function __construct(
        public readonly string $quoteId,
        public readonly array $payload,
    ) {}

    public function handle(
        RequestShippingQuoteUseCase $useCase,
        QuoteRequestFactory $factory,
        TransactionTracerPort $tracer,
    ): void {
        $tracer->nameTransaction('job ProcessShippingQuoteJob');
        $tracer->addAttributes(['quote.id' => $this->quoteId]);

        $request = $factory->fromArray($this->payload);

        $tracer->traceSegment('shipping.quote.process', fn () => $useCase->execute($this->quoteId, $request));
    }
}
