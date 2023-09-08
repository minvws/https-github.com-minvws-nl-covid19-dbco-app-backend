<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Models\Export\ExportType;
use App\Services\Export\ExportPlaceService;
use App\Services\MetricService;

class ApiExportPlaceController extends AbstractApiExportController
{
    public function __construct(
        ExportPlaceService $exportService,
        MetricService $metricService,
    ) {
        parent::__construct(ExportType::Place, $exportService, $metricService);
    }
}
