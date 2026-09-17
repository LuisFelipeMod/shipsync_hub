<?php

namespace App\Infrastructure\Aws;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Sqs\SqsClient;

final class AwsClientFactory
{
    /**
     * @param  array{
     *     key?: string|null,
     *     secret?: string|null,
     *     region?: string|null,
     *     endpoint?: string|null,
     *     use_path_style_endpoint?: bool|null,
     *     bucket?: string|null,
     *     dynamodb_table?: string|null,
     *     sqs_queue?: string|null
     * }  $config
     */
    public function __construct(private readonly array $config) {}

    public static function fromEnvironment(): self
    {
        return new self([
            'key' => getenv('AWS_ACCESS_KEY_ID') ?: 'test',
            'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: 'test',
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'endpoint' => getenv('AWS_ENDPOINT') ?: null,
            'use_path_style_endpoint' => filter_var(
                getenv('AWS_USE_PATH_STYLE_ENDPOINT') ?: 'true',
                FILTER_VALIDATE_BOOLEAN,
            ),
            'bucket' => getenv('AWS_BUCKET') ?: 'shipsync-local',
            'dynamodb_table' => getenv('DYNAMODB_TABLE') ?: 'shipsync-records',
            'sqs_queue' => getenv('SQS_QUEUE') ?: 'shipsync-jobs',
        ]);
    }

    /**
     * @param  array<string, mixed>  $aws
     */
    public static function fromLaravelConfig(array $aws): self
    {
        return new self($aws);
    }

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return $this->config;
    }

    public function sqs(): SqsClient
    {
        return new SqsClient($this->clientOptions());
    }

    public function dynamodb(): DynamoDbClient
    {
        return new DynamoDbClient($this->clientOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function clientOptions(): array
    {
        $options = [
            'version' => 'latest',
            'region' => $this->config['region'] ?? 'us-east-1',
            'credentials' => [
                'key' => $this->config['key'] ?? 'test',
                'secret' => $this->config['secret'] ?? 'test',
            ],
        ];

        $endpoint = $this->config['endpoint'] ?? null;
        if (is_string($endpoint) && $endpoint !== '') {
            $options['endpoint'] = $endpoint;
        }

        return $options;
    }
}
