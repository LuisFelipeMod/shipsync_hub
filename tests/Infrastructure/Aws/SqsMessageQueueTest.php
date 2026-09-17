<?php

use App\Application\Messaging\MessageQueuePort;
use App\Infrastructure\Aws\AwsClientFactory;
use App\Infrastructure\Aws\SqsMessageQueue;

describe('Adapter SQS (LocalStack)', function () {
    beforeEach(function () {
        $this->queue = new SqsMessageQueue(AwsClientFactory::fromEnvironment());
    });

    it('publica e consome uma mensagem na fila shipsync-jobs', function () {
        $body = 'shipsync-test-'.uniqid('', true);

        $messageId = $this->queue->publish($body);

        expect($messageId)->not->toBeEmpty();

        $received = null;
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $messages = $this->queue->receive(1);
            if ($messages !== []) {
                $received = $messages[0];
                break;
            }
            usleep(200_000);
        }

        expect($received)->not->toBeNull()
            ->and($received->body())->toBe($body);

        $this->queue->acknowledge($received->receiptHandle());
    });

    it('implementa o port de fila da aplicação', function () {
        expect($this->queue)->toBeInstanceOf(MessageQueuePort::class);
    });
});
