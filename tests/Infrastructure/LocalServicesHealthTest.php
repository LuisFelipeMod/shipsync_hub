<?php

describe('Saúde da infraestrutura local', function () {
    it('responde PONG no Redis (sessions e jobs)', function () {
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('REDIS_PORT') ?: 6379);

        $fp = @fsockopen($host, $port, $errno, $errstr, 2);

        expect($fp)->not->toBeFalse();

        fwrite($fp, "PING\r\n");
        $response = fgets($fp);
        fclose($fp);

        expect($response)->toContain('PONG');
    });

    it('aceita conexões no Memcached (cache de cotações)', function () {
        $host = getenv('MEMCACHED_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('MEMCACHED_PORT') ?: 11211);

        $fp = @fsockopen($host, $port, $errno, $errstr, 2);

        expect($fp)->not->toBeFalse();

        fwrite($fp, "stats\r\n");
        $response = fread($fp, 2048);
        fwrite($fp, "quit\r\n");
        fclose($fp);

        expect($response)->toContain('STAT');
    });

    it('aceita conexões AMQP no RabbitMQ', function () {
        $host = getenv('RABBITMQ_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('RABBITMQ_PORT') ?: 5672);

        $fp = @fsockopen($host, $port, $errno, $errstr, 2);

        expect($fp)->not->toBeFalse();
        fclose($fp);
    });

    it('expõe SQS, S3 e DynamoDB no LocalStack', function () {
        $endpoint = rtrim(getenv('AWS_ENDPOINT') ?: 'http://localhost:4566', '/');
        $health = @file_get_contents("{$endpoint}/_localstack/health");

        expect($health)->not->toBeFalse();

        $payload = json_decode($health, true);
        $services = $payload['services'] ?? [];

        expect($services)->toBeArray();

        foreach (['sqs', 's3', 'dynamodb'] as $service) {
            expect($services[$service] ?? null)->toBeIn(['available', 'running']);
        }
    });
});
