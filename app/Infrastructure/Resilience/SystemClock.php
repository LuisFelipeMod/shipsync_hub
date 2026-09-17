<?php

namespace App\Infrastructure\Resilience;

final class SystemClock implements Clock
{
    public function now(): int
    {
        return time();
    }
}
