<?php

declare(strict_types=1);

namespace App\Repositories\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Exceptions\Osiris\Client\ClientExceptionInterface;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\OsirisClient;
use App\Services\Osiris\SoapMessage\SoapMessageBuilderFactory;

final class SoapCaseExportRepository implements CaseExportRepository
{
    public function __construct(
        private readonly OsirisClient $osirisClient,
        private readonly SoapMessageBuilderFactory $builderFactory,
    ) {
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function exportCase(EloquentCase $case, CaseExportType $caseExportType): CaseExportResult
    {
        $builder = $this->builderFactory->build($case);
        $soapMessage = $builder->makeSoapMessage($caseExportType);

        try {
            $putMessageResult = $this->osirisClient->putMessage($soapMessage);
        } catch (ErrorResponseException $errorResponseException) {
            throw CaseExportRejectedException::fromErrorResponse($case, $errorResponseException);
        } catch (ClientExceptionInterface $clientException) {
            throw CaseExportException::fromThrowable($clientException);
        }

        return new CaseExportResult(
            $putMessageResult->osirisNumber,
            $builder->questionnaireVersion->value,
            $case->getReportNumber() ?? '',
            $case->uuid,
            $putMessageResult->warnings,
        );
    }
}
