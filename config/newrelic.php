<?php

return [

    /*
    |--------------------------------------------------------------------------
    | New Relic APM
    |--------------------------------------------------------------------------
    |
    | Em produção, instale a extensão PHP newrelic e defina NEW_RELIC_ENABLED=true.
    | Em local/testes o middleware e spans usam NoOp quando a extensão não existe.
    |
    */

    'enabled' => env('NEW_RELIC_ENABLED', false),

    'app_name' => env('NEW_RELIC_APP_NAME', env('APP_NAME', 'ShipSync Hub')),

    'license_key' => env('NEW_RELIC_LICENSE_KEY'),

    'distributed_tracing' => env('NEW_RELIC_DISTRIBUTED_TRACING', true),

];
