<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Osiris;

use App\Dto\Osiris\Client\PutMessageResult;
use App\Dto\Osiris\Repository\CaseExportResult;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Exceptions\Osiris\Client\ClientException;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\SoapCaseExportRepository;
use App\Services\Osiris\OsirisClient;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use App\Services\Osiris\SoapMessage\SoapMessageBuilderFactory;
use App\ValueObjects\OsirisNumber;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('osiris')]
#[Group('osiris-repo')]
final class SoapCaseExportRepositoryTest extends FeatureTestCase
{
    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    #[DataProvider('provideCaseExportTypes')]
    public function testExportCase(CaseExportType $caseExportType): void
    {
        $putMessageResult = new PutMessageResult(new OsirisNumber(1), []);
        $case = $this->createCase();

        $osirisClient = $this->createMock(OsirisClient::class);
        $osirisClient->expects($this->once())->method('putMessage')
            ->willReturn($putMessageResult);

        $osirisRepository = new SoapCaseExportRepository($osirisClient, App::get(SoapMessageBuilderFactory::class));
        $actual = $osirisRepository->exportCase($case, $caseExportType);

        $expected = new CaseExportResult(
            $putMessageResult->osirisNumber,
            QuestionnaireVersion::V10->value,
            $case->getReportNumber(),
            $case->uuid,
        );
        $this->assertEquals($expected, $actual);
    }

    public static function provideCaseExportTypes(): array
    {
        return [
            'type `initial`' => [CaseExportType::INITIAL_ANSWERS],
            'type `definitive`' => [CaseExportType::DEFINITIVE_ANSWERS],
            'type `deleted`' => [CaseExportType::DELETED_STATUS],
        ];
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function testRepositoryConvertsErrorResponseException(): void
    {
        $case = $this->createCase();

        $errorResponseException = new ErrorResponseException('reason', ['explanation']);
        $osirisClient = $this->createMock(OsirisClient::class);
        $osirisClient->expects($this->once())->method('putMessage')->willThrowException($errorResponseException);

        $this->expectExceptionObject(
            CaseExportRejectedException::fromErrorResponse($case, $errorResponseException),
        );

        $osirisRepository = new SoapCaseExportRepository($osirisClient, App::get(SoapMessageBuilderFactory::class));
        $osirisRepository->exportCase($case, $this->faker->randomElement(CaseExportType::cases()));
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function testRepositoryConvertsClientException(): void
    {
        $case = $this->createCase();

        $clientException = new ClientException('foo');
        $osirisClient = $this->createMock(OsirisClient::class);
        $osirisClient->expects($this->once())->method('putMessage')->willThrowException($clientException);

        $this->expectExceptionObject(CaseExportException::fromThrowable($clientException));

        $osirisRepository = new SoapCaseExportRepository($osirisClient, App::get(SoapMessageBuilderFactory::class));
        $osirisRepository->exportCase($case, $this->faker->randomElement(CaseExportType::cases()));
    }
}
