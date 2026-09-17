<?php

namespace App\Application\Messaging;

final readonly class ReceivedMessage
{
    public function __construct(
        private string $messageId,
        private string $body,
        private string $receiptHandle,
    ) {}

    public function messageId(): string
    {
        return $this->messageId;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function receiptHandle(): string
    {
        return $this->receiptHandle;
    }
}
