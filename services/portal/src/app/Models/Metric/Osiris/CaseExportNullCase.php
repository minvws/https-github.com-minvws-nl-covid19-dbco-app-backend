<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\CounterMetric;

final class CaseExportNullCase extends CounterMetric
{
    protected string $name = 'osiris:case_export:null_case';
    protected string $help = 'Case does not exist (anymore) when exporting case to Osiris';
}
