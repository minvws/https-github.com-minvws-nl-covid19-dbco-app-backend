<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\BaseCaseValidationEvent;
use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use Psr\Log\LoggerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class LogOsirisValidationResult
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function whenCaseValidationRaisesWarning(CaseValidationRaisesWarning $event): void
    {
        $this->log('Osiris validation warning(s) found', $event);
    }

    public function whenCaseValidationRaisesNotice(CaseValidationRaisesNotice $event): void
    {
        $this->log('Osiris validation notice(s) found', $event);
    }

    private function log(string $message, BaseCaseValidationEvent $event): void
    {
        $this->logger->info(
            $message,
            [
                'caseUuid' => $event->case->uuid,
                'caseExportType' => $event->caseExportType,
                'validationResult' => json_encode($event->validationResult, JSON_THROW_ON_ERROR),
            ],
        );
    }
}
