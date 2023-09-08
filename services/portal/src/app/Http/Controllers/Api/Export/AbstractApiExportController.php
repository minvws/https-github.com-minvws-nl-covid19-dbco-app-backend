<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Export\IndexRequest;
use App\Models\Enums\Api\Export\ExportApiParameter;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Metric\DataDisclosure\ExportRequests;
use App\Models\Metric\DataDisclosure\StreamRequest;
use App\Services\Export\Exceptions\ExportAuthorizationException;
use App\Services\Export\Exceptions\ExportCursorException;
use App\Services\Export\Exceptions\ExportNotFoundException;
use App\Services\Export\Exceptions\ExportPseudoIdException;
use App\Services\Export\ExportService;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use App\Services\MetricService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;

use function abort;
use function assert;
use function response;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

abstract class AbstractApiExportController extends ApiController
{
    public function __construct(
        protected readonly ExportType $auditObjectType,
        protected readonly ExportService $exportService,
        protected readonly MetricService $metricService,
    ) {
    }

    public function index(IndexRequest $request, Authenticatable $client): JsonResponse
    {
        assert($client instanceof ExportClient);

        try {
            $cursorToken = $request->getCursor();
            if (isset($cursorToken)) {
                $exportMode = ExportApiParameter::CURSOR;
                $cursor = $this->exportService->decodeCursorForClient($cursorToken, $client);
            } else {
                $exportMode = ExportApiParameter::SINCE_PARAMETER;
                $cursor = $this->exportService->createCursor($request->getSince(), $request->getUntil());
            }
        } catch (ExportCursorException) {
            abort(403);
        }

        $data = $this->exportService->listForClient($cursor, $client);

        $this->metricService->measure(
            new StreamRequest($this->auditObjectType->value, $client->id, $exportMode->value),
        );

        return response()->json($data, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function show(
        string $pseudoId,
        ExportPseudoIdHelper $pseudoIdHelper,
        Authenticatable $client,
        AuditEvent $auditEvent,
    ): JsonResponse {
        assert($client instanceof ExportClient);

        try {
            $id = $pseudoIdHelper->pseudoIdToIdForClient($pseudoId, $client);
            $auditEvent->object(AuditObject::create($this->auditObjectType->value, $id));
            $data = $this->exportService->exportForClient($id, $client);

            $this->metricService->measure(new ExportRequests($this->auditObjectType->value, $client->id));

            return response()->json($data, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (ExportAuthorizationException) {
            abort(403);
        } catch (ExportNotFoundException) {
            abort(404);
        } catch (ExportPseudoIdException) {
            abort(400);
        }
    }
}
