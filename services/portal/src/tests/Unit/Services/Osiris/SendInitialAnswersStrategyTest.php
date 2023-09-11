<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris;

use App\Exceptions\Osiris\CaseExport\CaseExportExceptionInterface;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Schema\Validation\ValidationRule;
use App\Services\Osiris\OsirisCaseExporter;
use App\Services\Osiris\SendInitialAnswersStrategy;
use App\Services\Osiris\Strategy\ExportableCaseProvider;
use Generator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('osiris')]
#[Group('osiris-case-export')]
final class SendInitialAnswersStrategyTest extends UnitTestCase
{
    private readonly ExportableCaseProvider $caseProvider;
    private readonly OsirisCaseExporter $caseExporter;
    private readonly SendInitialAnswersStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseProvider = Mockery::mock(ExportableCaseProvider::class);
        $this->caseExporter = Mockery::mock(OsirisCaseExporter::class);
        $this->strategy = new SendInitialAnswersStrategy($this->caseProvider, $this->caseExporter);
    }

    /**
     * @throws CaseExportExceptionInterface
     */
    public function testExecuteIsIdleIfExportableCaseNotFound(): void
    {
        $caseUuid = $this->faker->uuid();
        $this->caseProvider->expects('findValidCase')
            ->with($caseUuid, CaseExportType::INITIAL_ANSWERS, [ValidationRule::TAG_OSIRIS_INITIAL])
            ->andReturnNull();

        $this->caseExporter->expects('export')
            ->never();

        $this->strategy->execute($caseUuid);
    }

    /**
     * @throws CaseExportExceptionInterface
     */
    public function testExecuteInvokesCaseExporterIfExportableCaseIsFound(): void
    {
        $caseUuid = $this->faker->uuid();
        $case = Mockery::mock(EloquentCase::class);

        $this->caseProvider->expects('findValidCase')
            ->with($caseUuid, CaseExportType::INITIAL_ANSWERS, [ValidationRule::TAG_OSIRIS_INITIAL])
            ->andReturn($case);

        $this->caseExporter->expects('export')
            ->with($case, CaseExportType::INITIAL_ANSWERS);

        $this->strategy->execute($caseUuid);
    }

    #[DataProvider('provideCaseExportTypesWithSupportsExpectation')]
    public function testSupportsReturnsTrueForDeletedStatus(CaseExportType $caseExportType, bool $expectation): void
    {
        $this->assertEquals($expectation, $this->strategy->supports($caseExportType));
    }

    public static function provideCaseExportTypesWithSupportsExpectation(): Generator
    {
        yield 'case export type `initial`' => [CaseExportType::INITIAL_ANSWERS, true];
        yield 'case export type `definitive`' => [CaseExportType::DEFINITIVE_ANSWERS, false];
        yield 'case export type `deleted`' => [CaseExportType::DELETED_STATUS, false];
    }
}
