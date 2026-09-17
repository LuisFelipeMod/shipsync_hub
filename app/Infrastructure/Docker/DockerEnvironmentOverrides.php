<?php

namespace App\Infrastructure\Docker;

/**
 * O `.env` do host usa 127.0.0.1; dentro do Compose os serviços têm nomes DNS (redis, memcached, …).
 *
 * Deve rodar no início de `bootstrap/app.php`, antes de LoadConfiguration.
 */
final class DockerEnvironmentOverrides
{
    public static function applyIfRunningInContainer(): void
    {
        if (! is_file('/.dockerenv')) {
            return;
        }

        $overrides = [
            'REDIS_HOST' => 'redis',
            'REDIS_PORT' => '6379',
            'MEMCACHED_HOST' => 'memcached',
            'MEMCACHED_PORT' => '11211',
            'AWS_ENDPOINT' => 'http://localstack:4566',
            'PHP_CLI_SERVER_WORKERS' => '1',
            // UI /quotes e API dev: processa ProcessShippingQuoteJob na mesma requisição (sem worker Redis).
            'QUEUE_CONNECTION' => 'sync',
        ];

        foreach ($overrides as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }

        foreach (['REDIS_URL'] as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        if (($_ENV['REDIS_PASSWORD'] ?? null) === 'null' || ($_ENV['REDIS_PASSWORD'] ?? '') === '') {
            unset($_ENV['REDIS_PASSWORD'], $_SERVER['REDIS_PASSWORD']);
            putenv('REDIS_PASSWORD');
        }
    }
}
