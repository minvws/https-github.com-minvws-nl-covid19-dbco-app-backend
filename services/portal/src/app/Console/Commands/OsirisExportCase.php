<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\FeatureFlagHelper;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use Exception;
use Illuminate\Console\Command;
use Webmozart\Assert\Assert;

use function sprintf;

final class OsirisExportCase extends Command
{
    protected $signature = 'osiris:case-export {case} {case-export-type : One of CaseExportType enum values} {--queue}';

    protected $description = 'Export case to Osiris';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        if (FeatureFlagHelper::isDisabled('osiris_send_case_enabled')) {
            $this->info('Osiris case export is disabled');
            return;
        }

        $caseUuid = $this->argument('case');
        Assert::string($caseUuid);

        $caseExportType = CaseExportType::from((string) $this->argument('case-export-type'));

        $this->option('queue') === true
            ? ExportCaseToOsiris::dispatchIfEnabled($caseUuid, $caseExportType)
            : ExportCaseToOsiris::dispatchSync($caseUuid, $caseExportType);

        $this->info(sprintf('Dispatched `ExportCaseToOsiris` job to %s', $this->option('queue') === true ? 'queue' : 'sync queue'));
    }
}
