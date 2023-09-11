<?php

declare(strict_types=1);

return [
    'throw_exceptions' => env('OPENAPI_THROW_EXCEPTIONS', false),
    'specification' => env('OPENAPI_SPECIFICATION', '/shared/packages/portal-open-api/output/openapi.yaml'),
];
