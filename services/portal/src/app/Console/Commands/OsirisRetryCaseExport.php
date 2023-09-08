<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\FeatureFlagHelper;
use App\Services\Osiris\CaseExportRetryService;
use Illuminate\Console\Command;

class OsirisRetryCaseExport extends Command
{
    protected $signature = 'osiris:case-export:retry';

    protected $description = 'Retry exporting cases which should have been exported to Osiris';

    public function handle(CaseExportRetryService $caseExportRetryService): int
    {
        if (FeatureFlagHelper::isDisabled('osiris_retry_case_export_enabled')) {
            $this->error('Osiris retry case-export is disabled');

            return self::SUCCESS;
        }

        $this->info('Retry exporting notifications to Osiris for overdue cases');

        $caseExportRetryService->exportOverdueCases();

        return self::SUCCESS;
    }
}
