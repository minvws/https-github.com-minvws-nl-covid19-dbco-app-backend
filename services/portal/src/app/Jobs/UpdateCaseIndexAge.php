<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Config;
use App\Services\CaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Log\LoggerInterface;

class UpdateCaseIndexAge implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly string $caseUuid,
    ) {
        $this->onConnection(Config::string('misc.case.index_age.queue.connection'));
        $this->onQueue(Config::string('misc.case.index_age.queue.queue_name'));
    }

    public function uniqueId(): string
    {
        return $this->caseUuid;
    }

    public function handle(CaseService $caseService, LoggerInterface $logger): void
    {
        $case = $caseService->getCaseByUuid($this->caseUuid);

        if ($case === null) {
            $logger->debug('Case not found', [
                'uuid' => $this->caseUuid,
            ]);

            return;
        }

        $caseService->calculateIndexAgeForCase($case);

        $logger->debug('Case index-age updated', [
            'caseUuid' => $case->uuid,
        ]);
    }
}
