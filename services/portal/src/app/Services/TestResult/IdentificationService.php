<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Metric\TestResult\IdentificationStatus;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\Bsn\BsnService;
use App\Services\MetricService;
use Carbon\CarbonImmutable;
use Psr\Log\LoggerInterface;

final class IdentificationService
{
    public function __construct(
        private readonly BsnService $bsnService,
        private readonly LoggerInterface $logger,
        private readonly MetricService $metricService,
    ) {
    }

    public function identify(TestResultReport $testResultReport, EloquentOrganisation $organisation): ?PseudoBsn
    {
        $bsn = $testResultReport->person->bsn;
        $dateOfBirth = $testResultReport->person->dateOfBirth;

        if ($bsn === null) {
            $this->logger->info(
                'Skipping identification of index (bsn not given)',
                ['messageId' => $testResultReport->messageId],
            );
            $this->metricService->measure(IdentificationStatus::noBsnAvailable());

            return null;
        }

        $this->logger->info(
            'Started identification of index...',
            ['messageId' => $testResultReport->messageId],
        );

        try {
            $pseudoBsn = $this->bsnService->convertBsnAndDateOfBirthToPseudoBsn(
                $bsn,
                CarbonImmutable::instance($dateOfBirth),
                $organisation->external_id,
            );

            $this->logger->info(
                'Identification of index succeeded; retrieved pseudo BSN',
                ['messageId' => $testResultReport->messageId],
            );
            $this->metricService->measure(IdentificationStatus::identified());

            return $pseudoBsn;
        } catch (BsnException $bsnException) {
            $this->logger->notice(
                'Failed to identify index; pseudo BSN lookup failed',
                [
                    'reason' => $bsnException->getMessage(),
                    'messageId' => $testResultReport->messageId,
                ],
            );
            $this->metricService->measure(IdentificationStatus::notIdentified());

            return null;
        }
    }
}
