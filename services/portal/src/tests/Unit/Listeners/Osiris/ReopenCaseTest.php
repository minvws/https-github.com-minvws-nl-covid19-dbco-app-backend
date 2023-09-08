<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Listeners\Osiris\ReopenCase;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseLabelRepository;
use App\Repositories\CaseRepository;
use Generator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Faker\WithFaker;
use Tests\Unit\UnitTestCase;

class ReopenCaseTest extends UnitTestCase
{
    use WithFaker;

    private CaseRepository $caseRepository;
    private CaseLabelRepository $caseLabelRepository;
    private ReopenCase $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseRepository = Mockery::mock(CaseRepository::class);
        $this->caseLabelRepository = Mockery::mock(CaseLabelRepository::class);
        $this->listener = new ReopenCase($this->caseRepository, $this->caseLabelRepository);
    }

    #[DataProvider('provideIsReopenable')]
    public function testWhenCaseExportWasRejectedAddsLabelAndReopensCaseIfAllowed(bool $isReopenable): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $caseLabel = Mockery::mock(CaseLabel::class);

        $case->expects('isReopenable')
            ->andReturn($isReopenable);

        $this->caseLabelRepository->expects('getLabelByCode')
            ->andReturn($caseLabel);

        $this->caseRepository->expects('addCaseLabel')
            ->with($case, $caseLabel);

        $expectReopenCase = $this->caseRepository->expects('reopenCase');
        $isReopenable
            ? $expectReopenCase->once()->with($case)
            : $expectReopenCase->never();


        $this->listener->whenCaseExportWasRejected(
            new CaseExportRejected($case, $caseExportType, []),
        );
    }

    #[DataProvider('provideIsReopenable')]
    public function testWhenExportClientEncounteredErrorAddsLabelAndReopensCaseIfAllowed(bool $isReopenable): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $caseLabel = Mockery::mock(CaseLabel::class);

        $case->expects('isReopenable')
            ->andReturn($isReopenable);

        $this->caseLabelRepository->expects('getLabelByCode')
            ->andReturn($caseLabel);

        $this->caseRepository->expects('addCaseLabel')
            ->with($case, $caseLabel);

        $expectReopenCase = $this->caseRepository->expects('reopenCase');
        $isReopenable
            ? $expectReopenCase->once()->with($case)
            : $expectReopenCase->never();


        $this->listener->whenExportClientEncounteredError(
            new CaseExportFailed($case, $caseExportType),
        );
    }

    #[DataProvider('provideIsReopenable')]
    public function testWhenCaseValidationRaisesWarningReopensCaseIfAllowed(bool $isReopenable): void
    {
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());

        $case->expects('isReopenable')
            ->andReturn($isReopenable);

        $this->caseLabelRepository->expects('getLabelByCode')
            ->never();

        $this->caseRepository->expects('addCaseLabel')
            ->never();

        $expectReopenCase = $this->caseRepository->expects('reopenCase');
        $isReopenable
            ? $expectReopenCase->once()->with($case)
            : $expectReopenCase->never();


        $this->listener->whenCaseValidationRaisesWarning(
            new CaseValidationRaisesWarning($case, [], $caseExportType),
        );
    }

    public static function provideIsReopenable(): Generator
    {
        yield 'allowed to reopen' => [true];
        yield 'not allowed to reopen' => [false];
    }
}
