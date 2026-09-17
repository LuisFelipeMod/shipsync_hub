<?php

namespace App\Infrastructure\Resilience;

final class ExponentialBackoffWithJitter
{
    /** @param \Closure(int $maxJitterMs): int|null $jitter */
    public function __construct(
        private readonly int $baseMs,
        private readonly int $maxMs,
        private readonly int $maxAttempts,
        private readonly ?\Closure $jitter = null,
    ) {}

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function delayMs(int $attemptAfterFailure): int
    {
        $attemptAfterFailure = max(1, $attemptAfterFailure);
        $exponential = $this->baseMs * (2 ** ($attemptAfterFailure - 1));
        $capped = min($this->maxMs, $exponential);
        $maxJitter = (int) floor($capped * 0.25);
        $jitterMs = $this->jitter !== null
            ? ($this->jitter)($maxJitter)
            : random_int(0, max(0, $maxJitter));

        return min($this->maxMs, $capped + $jitterMs);
    }
}
