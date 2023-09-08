<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Models\Export\ExportType;
use App\Services\Export\ExportEventService;
use App\Services\MetricService;

class ApiExportEventController extends AbstractApiExportController
{
    public function __construct(
        ExportEventService $exportService,
        MetricService $metricService,
    ) {
        parent::__construct(ExportType::Event, $exportService, $metricService);
    }
}
