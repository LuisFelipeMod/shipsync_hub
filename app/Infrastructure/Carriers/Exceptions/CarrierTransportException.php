<?php

namespace App\Infrastructure\Carriers\Exceptions;

use App\Infrastructure\Resilience\RetryableCarrierFailure;
use RuntimeException;

/** Falha transitória de HTTP/transporte — elegível a retry com backoff. */
final class CarrierTransportException extends RuntimeException implements RetryableCarrierFailure
{
}
