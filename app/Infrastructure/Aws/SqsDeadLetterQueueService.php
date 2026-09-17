<?php

namespace App\Infrastructure\Aws;

use App\Application\Messaging\ReceivedMessage;

final class SqsDeadLetterQueueService
{
    public function __construct(
        private readonly SqsMessageQueue $deadLetterQueue,
        private readonly SqsMessageQueue $mainQueue,
    ) {}

    /**
     * @return array{
     *     approximate_visible: int,
     *     approximate_not_visible: int
     * }
     */
    public function stats(): array
    {
        $attributes = $this->deadLetterQueue->queueAttributes([
            'ApproximateNumberOfMessages',
            'ApproximateNumberOfMessagesNotVisible',
        ]);

        return [
            'approximate_visible' => (int) ($attributes['ApproximateNumberOfMessages'] ?? 0),
            'approximate_not_visible' => (int) ($attributes['ApproximateNumberOfMessagesNotVisible'] ?? 0),
        ];
    }

    /**
     * @return list<ReceivedMessage>
     */
    public function peek(int $maxMessages = 10): array
    {
        return $this->deadLetterQueue->receive(min(max($maxMessages, 1), 10));
    }

    public function reprocess(int $maxMessages = 10): int
    {
        $messages = $this->peek($maxMessages);
        $count = 0;

        foreach ($messages as $message) {
            $this->mainQueue->publish($message->body());
            $this->deadLetterQueue->acknowledge($message->receiptHandle());
            $count++;
        }

        return $count;
    }
}
