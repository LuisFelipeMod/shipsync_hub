<?php

namespace App\Infrastructure\Resilience;

interface Sleeper
{
    public function sleepMs(int $milliseconds): void;
}
