<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Swagger UI (OpenAPI)
    |--------------------------------------------------------------------------
    |
    | Spec versionada em docs/openapi/v1/openapi.yaml (alinhada a /api/v1).
    | Desligue em produção ou use OPENAPI_UI_ENABLED=false.
    |
    */

    'ui_enabled' => (bool) env('OPENAPI_UI_ENABLED', env('APP_ENV') === 'local'),

    'spec_version' => 'v1',

    'spec_path' => base_path('docs/openapi/v1/openapi.yaml'),

    'ui_path' => env('OPENAPI_UI_PATH', 'api/documentation'),

    'spec_url_path' => env('OPENAPI_SPEC_PATH', 'docs/openapi/v1/openapi.yaml'),

];
