<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\CaseExportSucceeded;
use Psr\Log\LoggerInterface;

final readonly class ProcessOsirisNumber
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CaseExportSucceeded $event): void
    {
        $osirisNumber = $event->caseExportResult->osirisNumber->toInt();

        if ($event->case->osirisNumber === $osirisNumber) {
            return;
        }

        if ($event->case->osirisNumber === null) {
            $event->case->osirisNumber = $osirisNumber;
            $event->case->save();

            return;
        }

        $this->logger->warning(
            'Mismatch between Osiris number received in response and stored in database',
            [
                'osirisNumberInResponse' => $osirisNumber,
                'osirisNumberInDatabase' => $event->case->osirisNumber,
                'caseUuid' => $event->case->uuid,
                'caseExportType' => $event->caseExportType->value,
            ],
        );
    }
}
