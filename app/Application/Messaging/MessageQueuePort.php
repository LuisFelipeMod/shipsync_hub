<?php

namespace App\Application\Messaging;

interface MessageQueuePort
{
    public function publish(string $body): string;

    /**
     * @return list<ReceivedMessage>
     */
    public function receive(int $maxMessages = 1): array;

    public function acknowledge(string $receiptHandle): void;
}
