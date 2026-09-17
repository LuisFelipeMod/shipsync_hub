<?php

namespace App\Infrastructure\Aws;

use App\Application\Messaging\MessageQueuePort;
use App\Application\Messaging\ReceivedMessage;

final class SqsMessageQueue implements MessageQueuePort
{
    private ?string $queueUrl = null;

    public function __construct(
        private readonly AwsClientFactory $factory,
        private readonly ?string $queueName = null,
    ) {}

    public static function main(AwsClientFactory $factory): self
    {
        return new self($factory, (string) ($factory->config()['sqs_queue'] ?? 'shipsync-jobs'));
    }

    public static function deadLetter(AwsClientFactory $factory): self
    {
        return new self($factory, (string) ($factory->config()['sqs_dlq'] ?? 'shipsync-jobs-dlq'));
    }

    /**
     * @param  list<string>  $names
     * @return array<string, string>
     */
    public function queueAttributes(array $names): array
    {
        $result = $this->factory->sqs()->getQueueAttributes([
            'QueueUrl' => $this->queueUrl(),
            'AttributeNames' => $names,
        ]);

        return $result['Attributes'] ?? [];
    }

    public function publish(string $body): string
    {
        $result = $this->factory->sqs()->sendMessage([
            'QueueUrl' => $this->queueUrl(),
            'MessageBody' => $body,
        ]);

        return (string) $result['MessageId'];
    }

    public function receive(int $maxMessages = 1): array
    {
        $result = $this->factory->sqs()->receiveMessage([
            'QueueUrl' => $this->queueUrl(),
            'MaxNumberOfMessages' => min(max($maxMessages, 1), 10),
            'WaitTimeSeconds' => 2,
        ]);

        $messages = [];

        foreach ($result['Messages'] ?? [] as $message) {
            $messages[] = new ReceivedMessage(
                (string) $message['MessageId'],
                (string) $message['Body'],
                (string) $message['ReceiptHandle'],
            );
        }

        return $messages;
    }

    public function acknowledge(string $receiptHandle): void
    {
        $this->factory->sqs()->deleteMessage([
            'QueueUrl' => $this->queueUrl(),
            'ReceiptHandle' => $receiptHandle,
        ]);
    }

    private function queueUrl(): string
    {
        if ($this->queueUrl !== null) {
            return $this->queueUrl;
        }

        $queueName = $this->queueName ?? (string) ($this->factory->config()['sqs_queue'] ?? 'shipsync-jobs');
        $result = $this->factory->sqs()->getQueueUrl(['QueueName' => $queueName]);
        $this->queueUrl = (string) $result['QueueUrl'];

        return $this->queueUrl;
    }
}
