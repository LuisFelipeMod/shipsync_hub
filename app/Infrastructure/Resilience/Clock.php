<?php

namespace App\Infrastructure\Resilience;

interface Clock
{
    public function now(): int;
}
