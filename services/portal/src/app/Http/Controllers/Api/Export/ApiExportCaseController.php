<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Models\Export\ExportType;
use App\Services\Export\ExportCaseService;
use App\Services\MetricService;

class ApiExportCaseController extends AbstractApiExportController
{
    public function __construct(
        ExportCaseService $exportService,
        MetricService $metricService,
    ) {
        parent::__construct(ExportType::Case_, $exportService, $metricService);
    }
}
