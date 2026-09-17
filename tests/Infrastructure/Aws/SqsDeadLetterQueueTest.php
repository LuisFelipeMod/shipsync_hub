<?php

use App\Infrastructure\Aws\AwsClientFactory;
use App\Infrastructure\Aws\SqsDeadLetterQueueService;
use App\Infrastructure\Aws\SqsMessageQueue;

describe('DLQ SQS (LocalStack)', function () {
    beforeEach(function () {
        $factory = AwsClientFactory::fromEnvironment();
        $this->service = new SqsDeadLetterQueueService(
            SqsMessageQueue::deadLetter($factory),
            SqsMessageQueue::main($factory),
        );
        $this->dlq = SqsMessageQueue::deadLetter($factory);
    });

    it('reprocessa mensagem publicada diretamente na DLQ', function () {
        $body = 'dlq-reprocess-'.uniqid('', true);
        $this->dlq->publish($body);

        $reprocessed = $this->service->reprocess(1);

        expect($reprocessed)->toBe(1);

        $main = SqsMessageQueue::main(AwsClientFactory::fromEnvironment());
        $received = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $messages = $main->receive(1);
            if ($messages !== [] && $messages[0]->body() === $body) {
                $received = $messages[0];
                break;
            }
            usleep(200_000);
        }

        expect($received)->not->toBeNull();
        $main->acknowledge($received->receiptHandle());
    });

    it('expõe estatísticas aproximadas da fila DLQ', function () {
        $stats = $this->service->stats();

        expect($stats)->toHaveKeys(['approximate_visible', 'approximate_not_visible']);
    });
});
