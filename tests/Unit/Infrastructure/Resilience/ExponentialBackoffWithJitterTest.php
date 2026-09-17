<?php

use App\Infrastructure\Resilience\ExponentialBackoffWithJitter;

describe('ExponentialBackoffWithJitter', function () {
    it('limita tentativas e cresce o delay exponencialmente sem jitter', function () {
        $backoff = new ExponentialBackoffWithJitter(
            baseMs: 100,
            maxMs: 1000,
            maxAttempts: 3,
            jitter: static fn (int $max): int => 0,
        );

        expect($backoff->maxAttempts())->toBe(3)
            ->and($backoff->delayMs(1))->toBe(100)
            ->and($backoff->delayMs(2))->toBe(200)
            ->and($backoff->delayMs(3))->toBe(400);
    });

    it('não ultrapassa o teto configurado', function () {
        $backoff = new ExponentialBackoffWithJitter(
            baseMs: 500,
            maxMs: 800,
            maxAttempts: 5,
            jitter: static fn (int $max): int => $max,
        );

        expect($backoff->delayMs(4))->toBe(800);
    });
});
