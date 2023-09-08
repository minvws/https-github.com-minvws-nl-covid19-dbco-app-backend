<?php

declare(strict_types=1);

namespace App\Models\Metric\TestResult;

use App\Models\Metric\CounterMetric;

final class ReportingNotAllowedForOrganisation extends CounterMetric
{
    protected string $name = 'test_result_report_import:reporting_not_allowed_for_organisation';
    protected string $help = 'Total unprocessed test results reports caused by a disabled submission flag for organisation';

    public function __construct(string $ggdIdentifier)
    {
        $this->labels = ['ggdIdentifier' => $ggdIdentifier];
    }
}
