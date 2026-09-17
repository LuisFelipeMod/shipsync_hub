<?php

namespace App\Infrastructure\Resilience;

final readonly class CircuitBreakerSnapshot
{
    public const STATE_CLOSED = 'closed';

    public const STATE_OPEN = 'open';

    public const STATE_HALF_OPEN = 'half_open';

    public function __construct(
        public string $state,
        public int $failureCount,
        public ?int $openedAtEpoch,
    ) {}

    public static function closed(): self
    {
        return new self(self::STATE_CLOSED, 0, null);
    }
}
