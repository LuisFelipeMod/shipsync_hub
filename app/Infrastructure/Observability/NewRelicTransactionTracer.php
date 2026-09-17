<?php

namespace App\Infrastructure\Observability;

use App\Application\Observability\TransactionTracerPort;
use Throwable;

final class NewRelicTransactionTracer implements TransactionTracerPort
{
    public function isEnabled(): bool
    {
        if (! extension_loaded('newrelic')) {
            return false;
        }

        try {
            return (bool) config('newrelic.enabled', false);
        } catch (Throwable) {
            $raw = getenv('NEW_RELIC_ENABLED');

            return $raw !== false && filter_var($raw, FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function nameTransaction(string $name): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        newrelic_name_transaction($name);
    }

    public function addAttributes(array $attributes): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        foreach ($attributes as $key => $value) {
            if ($value === null) {
                continue;
            }

            newrelic_add_custom_parameter((string) $key, $value);
        }
    }

    public function traceSegment(string $name, callable $callback)
    {
        if (! $this->isEnabled()) {
            return $callback();
        }

        if (function_exists('newrelic_start_segment')) {
            $segment = newrelic_start_segment($name);

            try {
                return $callback();
            } catch (Throwable $exception) {
                if (function_exists('newrelic_notice_error')) {
                    newrelic_notice_error($exception);
                }

                throw $exception;
            } finally {
                if (is_object($segment) && method_exists($segment, 'end')) {
                    $segment->end();
                }
            }
        }

        $this->addAttributes(['segment' => $name]);

        return $callback();
    }
}
