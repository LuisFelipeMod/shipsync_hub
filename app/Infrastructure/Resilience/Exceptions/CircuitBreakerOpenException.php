<?php

namespace App\Infrastructure\Resilience\Exceptions;

use RuntimeException;

final class CircuitBreakerOpenException extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('Circuit breaker aberto para integração "%s".', $key));
    }
}
