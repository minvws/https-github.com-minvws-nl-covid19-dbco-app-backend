<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Dto\OsirisHistory\OsirisHistoryDto;
use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseExportSucceeded;
use App\Events\Osiris\CaseNotExportable;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Listeners\Osiris\CreateOsirisHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\HistoryRepository;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use App\ValueObjects\OsirisNumber;
use Generator;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Faker\WithFaker;
use Tests\Unit\UnitTestCase;

class CreateOsirisHistoryTest extends UnitTestCase
{
    use WithFaker;

    private HistoryRepository $historyRepository;
    private CreateOsirisHistory $createOsirisHistory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->historyRepository = Mockery::mock(HistoryRepository::class);
        $this->createOsirisHistory = new CreateOsirisHistory($this->historyRepository);
    }

    #[DataProvider('provideCaseExportTypes')]
    public function testWhenCaseNotExportableAddsAnOsirisHistoryRecord(CaseExportType $caseExportType, string $osirisStatus): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();

        $this->historyRepository->expects('addToOsirisHistory')
            ->withArgs(function (OsirisHistoryDto $arg) use ($case, $osirisStatus) {
                $this->assertEquals($case->uuid, $arg->caseUuid);
                $this->assertEquals(OsirisHistoryStatus::blocked(), $arg->status);
                $this->assertEquals($osirisStatus, $arg->osirisStatus);
                $this->assertNull($arg->osirisValidationResponse);

                return true;
            });

        $this->createOsirisHistory->whenCaseNotExportable(
            new CaseNotExportable($case, $caseExportType),
        );
    }

    #[DataProvider('provideCaseExportTypes')]
    public function testWhenCaseValidationRaisesWarningAddsAnOsirisHistoryRecord(CaseExportType $caseExportType, string $osirisStatus): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();

        $this->historyRepository->expects('addToOsirisHistory')
            ->withArgs(function (OsirisHistoryDto $arg) use ($case, $osirisStatus) {
                $this->assertEquals($case->uuid, $arg->caseUuid);
                $this->assertEquals(OsirisHistoryStatus::validation(), $arg->status);
                $this->assertEquals($osirisStatus, $arg->osirisStatus);
                $this->assertInstanceOf(OsirisHistoryValidationResponse::class, $arg->osirisValidationResponse);

                return true;
            });

        $this->createOsirisHistory->whenCaseValidationRaisesWarning(
            new CaseValidationRaisesWarning($case, [], $caseExportType),
        );
    }

    #[DataProvider('provideCaseExportTypes')]
    public function testWhenCaseExportSucceededAddsAnOsirisHistoryRecord(CaseExportType $caseExportType, string $osirisStatus): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();
        $result = new CaseExportResult(
            new OsirisNumber($this->faker->randomNumber(6)),
            $this->faker->randomNumber(1),
            $this->faker->numerify('######'),
            $this->faker->uuid(),
        );

        $this->historyRepository->expects('addToOsirisHistory')
            ->withArgs(function (OsirisHistoryDto $arg) use ($case, $osirisStatus) {
                $this->assertEquals($case->uuid, $arg->caseUuid);
                $this->assertEquals(OsirisHistoryStatus::success(), $arg->status);
                $this->assertEquals($osirisStatus, $arg->osirisStatus);
                $this->assertInstanceOf(OsirisHistoryValidationResponse::class, $arg->osirisValidationResponse);

                return true;
            });

        $this->createOsirisHistory->whenCaseExportSucceeded(
            new CaseExportSucceeded($case, $result, $caseExportType),
        );
    }

    #[DataProvider('provideCaseExportTypes')]
    public function testWhenCaseExportWasRejectedAddsAnOsirisHistoryRecord(CaseExportType $caseExportType, string $osirisStatus): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();

        $this->historyRepository->expects('addToOsirisHistory')
            ->withArgs(function (OsirisHistoryDto $arg) use ($case, $osirisStatus) {
                $this->assertEquals($case->uuid, $arg->caseUuid);
                $this->assertEquals(OsirisHistoryStatus::failed(), $arg->status);
                $this->assertEquals($osirisStatus, $arg->osirisStatus);
                $this->assertInstanceOf(OsirisHistoryValidationResponse::class, $arg->osirisValidationResponse);

                return true;
            });

        $this->createOsirisHistory->whenCaseExportWasRejected(
            new CaseExportRejected($case, $caseExportType, []),
        );
    }

    #[DataProvider('provideCaseExportTypes')]
    public function testWhenExportClientEncounteredErrorAddsAnOsirisHistoryRecord(CaseExportType $caseExportType, string $osirisStatus): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();

        $this->historyRepository->expects('addToOsirisHistory')
            ->withArgs(function (OsirisHistoryDto $arg) use ($case, $osirisStatus) {
                $this->assertEquals($case->uuid, $arg->caseUuid);
                $this->assertEquals(OsirisHistoryStatus::failed(), $arg->status);
                $this->assertEquals($osirisStatus, $arg->osirisStatus);
                $this->assertNull($arg->osirisValidationResponse);

                return true;
            });

        $this->createOsirisHistory->whenExportClientEncounteredError(
            new CaseExportFailed($case, $caseExportType),
        );
    }

    public static function provideCaseExportTypes(): Generator
    {
        yield 'initial answers' => [CaseExportType::INITIAL_ANSWERS, SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL];
        yield 'definitive answers' => [CaseExportType::DEFINITIVE_ANSWERS, SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE];
        yield 'deleted status' => [CaseExportType::DELETED_STATUS, SoapMessageBuilder::NOTIFICATION_STATUS_DELETED];
    }
}
