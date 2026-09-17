<?php

return [

    'quote_cache_ttl_seconds' => (int) env('SHIPPING_QUOTE_CACHE_TTL', 3600),

    'resilience' => [
        'enabled' => filter_var(env('SHIPPING_RESILIENCE_ENABLED', true), FILTER_VALIDATE_BOOL),
        'max_attempts' => (int) env('SHIPPING_CARRIER_MAX_ATTEMPTS', 3),
        'backoff_base_ms' => (int) env('SHIPPING_CARRIER_BACKOFF_BASE_MS', 100),
        'backoff_max_ms' => (int) env('SHIPPING_CARRIER_BACKOFF_MAX_MS', 5000),
        'circuit_key' => env('SHIPPING_CARRIER_CIRCUIT_KEY', 'default-carrier'),
        'circuit_failure_threshold' => (int) env('SHIPPING_CARRIER_CIRCUIT_FAILURE_THRESHOLD', 5),
        'circuit_open_seconds' => (int) env('SHIPPING_CARRIER_CIRCUIT_OPEN_SECONDS', 30),
        'circuit_state_ttl_seconds' => (int) env('SHIPPING_CARRIER_CIRCUIT_STATE_TTL', 3600),
    ],

];
