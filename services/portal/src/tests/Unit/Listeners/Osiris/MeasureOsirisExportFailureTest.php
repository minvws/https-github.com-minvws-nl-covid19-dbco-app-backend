<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Osiris;

use App\Events\Osiris\CaseExportRejected;
use App\Listeners\Osiris\MeasureOsirisExportFailure;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\Metric\Osiris\CaseExportFailed;
use App\Models\Metric\Osiris\CaseExportNullCase;
use App\Models\Metric\Osiris\ValidationResponse;
use App\Services\MetricService;
use Mockery;
use Tests\Faker\WithFaker;
use Tests\Unit\UnitTestCase;

class MeasureOsirisExportFailureTest extends UnitTestCase
{
    use WithFaker;

    private MetricService $metricService;
    private MeasureOsirisExportFailure $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metricService = Mockery::mock(MetricService::class);
        $this->listener = new MeasureOsirisExportFailure($this->metricService);
    }

    public function testWhenCaseExportWasRejectedInvokesMetricService(): void
    {
        $event = new CaseExportRejected(
            Mockery::mock(EloquentCase::class),
            $this->faker->randomElement(CaseExportType::cases()),
            $errors = $this->faker->randomElements(['error'], $this->faker->numberBetween(0, 1)),
        );
        $this->metricService->expects('measure')
            ->withArgs(function (CaseExportFailed $arg) use ($errors): bool {
                $this->assertSame('rejected', $arg->getLabels()['status']);
                $this->assertSame(
                    empty($errors) ? ValidationResponse::None->value : ValidationResponse::HasErrors->value,
                    $arg->getLabels()['validation_response'],
                );

                return true;
            });

        $this->listener->whenCaseExportWasRejected($event);
    }

    public function testWhenExportClientEncounteredErrorInvokesMetricService(): void
    {
        $this->metricService->expects('measure')
            ->withArgs(function (CaseExportFailed $arg): bool {
                $this->assertSame('failed', $arg->getLabels()['status']);
                $this->assertSame(ValidationResponse::NotApplicable->value, $arg->getLabels()['validation_response']);

                return true;
            });

        $this->listener->whenExportClientEncounteredError();
    }

    public function testCaseExportNullCaseMetricIsPassedToService(): void
    {
        $this->metricService->expects('measure')
            ->withArgs(static fn ($arg) => $arg instanceof CaseExportNullCase);

        $this->listener->whenCaseForExportIsMissing();
    }
}
