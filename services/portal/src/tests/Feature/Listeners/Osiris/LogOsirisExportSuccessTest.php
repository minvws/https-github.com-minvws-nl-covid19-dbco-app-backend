<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Events\Osiris\CaseExportSucceeded;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\Metric\Osiris\CaseExportSucceeded as CaseExportSucceededMetric;
use App\Models\Metric\Osiris\ValidationResponse;
use App\Repositories\Osiris\CaseExportRepository;
use App\Services\MetricService;
use App\Services\Osiris\OsirisCaseExporter;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use App\ValueObjects\OsirisNumber;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use Tests\Feature\FeatureTestCase;
use Webmozart\Assert\Assert;

#[Group('osiris')]
class LogOsirisExportSuccessTest extends FeatureTestCase
{
    /**
     * @throws CaseExportRejectedException
     * @throws CaseExportException
     * @throws Exception
     */
    public function testOsirisFinalNotificationWasSuccessfulWithWarningsInSuccessEvent(): void
    {
        Event::fake();

        $case = $this->createCaseExportableToOsiris();

        $this->createMockedCaseExporter(['Warning 1'])
            ->export($case, CaseExportType::DEFINITIVE_ANSWERS);

        Event::assertDispatched(CaseExportSucceeded::class, static function (CaseExportSucceeded $e) {
            return !empty($e->caseExportResult->warnings);
        });
    }

    /**
     * @throws CaseExportRejectedException
     * @throws CaseExportException
     * @throws Exception
     */
    public function testOsirisSuccessfullNotificationWithWarningsHasMetricWithLabel(): void
    {
        Event::fake([
            JobProcessed::class,
        ]);

        $case = $this->createCaseExportableToOsiris();

        $this->mock(MetricService::class, static function (MockInterface $mock): void {
            $mock->expects('measure')
                ->withArgs(static function (CaseExportSucceededMetric $metric): bool {
                    Assert::keyExists($metric->getLabels(), 'validation_response');
                    Assert::eq($metric->getLabels()['validation_response'], ValidationResponse::HasWarnings->value);
                    return true;
                });
        });

        $this->createMockedCaseExporter(['Warning 1'])
            ->export($case, CaseExportType::DEFINITIVE_ANSWERS);
    }

    /**
     * @throws CaseExportRejectedException
     * @throws CaseExportException
     * @throws Exception
     */
    public function testOsirisSuccessfullNotificationWithoutWarningsHasMetricWithLabel(): void
    {
        Event::fake([
            JobProcessed::class,
        ]);

        $case = $this->createCaseExportableToOsiris();

        $this->mock(MetricService::class, static function (MockInterface $mock): void {
            $mock->expects('measure')
                ->withArgs(static function (CaseExportSucceededMetric $metric): bool {
                    Assert::keyExists($metric->getLabels(), 'validation_response');
                    Assert::eq($metric->getLabels()['validation_response'], ValidationResponse::None->value);
                    return true;
                });
        });

        $this->createMockedCaseExporter()
            ->export($case, CaseExportType::DEFINITIVE_ANSWERS);
    }

    /**
     * @param array<string> $warnings
     *
     * @throws Exception
     */
    private function createMockedCaseExporter(array $warnings = []): OsirisCaseExporter
    {
        $repository = $this->createMock(CaseExportRepository::class);
        $repository->expects($this->once())
            ->method('exportCase')
            ->willReturn(
                new CaseExportResult(
                    new OsirisNumber($this->faker->randomNumber(6)),
                    $this->faker->randomElement(QuestionnaireVersion::cases())->value,
                    $this->faker->bothify('?#?-#?#-?#?'),
                    $this->faker->uuid(),
                    $warnings,
                ),
            );

        return new OsirisCaseExporter($repository);
    }
}
