<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Http\Controllers\Api\ApiController;
use App\Services\Export\JSONSchemaService;
use Illuminate\Http\JsonResponse;

use function abort_if;
use function is_string;

class ApiSchemaController extends ApiController
{
    public function show(string $path, JSONSchemaService $jsonSchemaService): JsonResponse
    {
        $schema = $jsonSchemaService->getJSONSchemaForPath($path);
        abort_if(!is_string($schema), 404);
        return JsonResponse::fromJsonString($schema, 200, ['Content-Type' => 'application/schema+json']);
    }
}
