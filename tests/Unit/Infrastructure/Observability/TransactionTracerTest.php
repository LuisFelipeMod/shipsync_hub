<?php

use App\Infrastructure\Observability\NewRelicTransactionTracer;
use App\Infrastructure\Observability\NoOpTransactionTracer;

describe('TransactionTracer', function () {
    it('NoOp executa segmentos sem efeito colateral', function () {
        $tracer = new NoOpTransactionTracer;

        expect($tracer->isEnabled())->toBeFalse()
            ->and($tracer->traceSegment('test.segment', fn () => 42))->toBe(42);
    });

    it('New Relic fica desligado sem extensão PHP', function () {
        $tracer = new NewRelicTransactionTracer;

        expect(extension_loaded('newrelic'))->toBeFalse()
            ->and($tracer->isEnabled())->toBeFalse();
    });
});
