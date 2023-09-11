<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Events\Osiris\ExportableCaseNotFound;
use App\Exceptions\Osiris\CaseExport\CaseExportExceptionInterface;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseRepository;
use App\Services\Osiris\OsirisCaseExporter;
use App\Services\Osiris\SendDeletedStatusStrategy;
use Generator;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Faker\WithFaker;
use Tests\Feature\FeatureTestCase;

#[Group('osiris')]
#[Group('osiris-case-export')]
final class SendDeletedStatusStrategyTest extends FeatureTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseRepository = Mockery::mock(CaseRepository::class);
        $this->caseExporter = Mockery::mock(OsirisCaseExporter::class);
        $this->strategy = new SendDeletedStatusStrategy($this->caseRepository, $this->caseExporter);
    }

    /**
     * @throws CaseExportExceptionInterface
     */
    public function testExecuteDispatchesEventIfCaseNotFound(): void
    {
        Event::fake();
        $caseUuid = $this->faker->uuid();

        $this->caseRepository->expects('getCaseIncludingSoftDeletes')
            ->with($caseUuid)
            ->andReturnNull();

        $this->caseExporter->expects('export')
            ->never();

        $this->strategy->execute($caseUuid);

        Event::assertDispatched(ExportableCaseNotFound::class);
    }

    /**
     * @throws CaseExportExceptionInterface
     */
    public function testExecuteInvokesCaseExporterIfExportableCaseIsFound(): void
    {
        $caseUuid = $this->faker->uuid();
        $case = Mockery::mock(EloquentCase::class);

        $this->caseRepository->expects('getCaseIncludingSoftDeletes')
            ->with($caseUuid)
            ->andReturn($case);

        $this->caseExporter->expects('export')
            ->with($case, CaseExportType::DELETED_STATUS);

        $this->strategy->execute($caseUuid);
    }

    #[DataProvider('provideCaseExportTypesWithSupportsExpectation')]
    public function testSupportsReturnsTrueForDeletedStatus(CaseExportType $caseExportType, bool $expectation): void
    {
        $this->assertEquals($expectation, $this->strategy->supports($caseExportType));
    }

    public static function provideCaseExportTypesWithSupportsExpectation(): Generator
    {
        yield 'case export type `initial`' => [CaseExportType::INITIAL_ANSWERS, false];
        yield 'case export type `definitive`' => [CaseExportType::DEFINITIVE_ANSWERS, false];
        yield 'case export type `deleted`' => [CaseExportType::DELETED_STATUS, true];
    }
}
