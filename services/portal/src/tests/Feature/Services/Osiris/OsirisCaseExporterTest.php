<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseExportSucceeded;
use App\Events\Osiris\CaseNotExportable;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase\Index;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\CaseExportRepository;
use App\Services\Osiris\OsirisCaseExporter;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use App\ValueObjects\OsirisNumber;
use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use MinVWS\DBCO\Enum\Models\Gender;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

#[Group('osiris')]
final class OsirisCaseExporterTest extends FeatureTestCase
{
    private CaseExportRepository $osirisRepository;
    private OsirisCaseExporter $caseExporter;

    public function setUp(): void
    {
        parent::setUp();

        $this->osirisRepository = Mockery::mock(CaseExportRepository::class);
        $this->caseExporter = new OsirisCaseExporter($this->osirisRepository);
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function testDispatchCaseExportSucceededEventOnSuccess(): void
    {
        Event::fake();
        $case = $this->createCaseExportableToOsiris();

        $this->osirisRepository->expects('exportCase')
            ->andReturns(
                new CaseExportResult(
                    new OsirisNumber($this->faker->randomNumber(6)),
                    QuestionnaireVersion::V10->value,
                    $this->faker->word(),
                    $case->uuid,
                ),
            );

        $this->caseExporter->export($case, $this->faker->randomElement(CaseExportType::cases()));

        Event::assertDispatched(CaseExportSucceeded::class);
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function testDispatchOsirisExportBlockedEventIfCaseHasHPZoneNumber(): void
    {
        Event::fake();
        $case = $this->createCase(['hpzone_number' => $this->faker->randomNumber(6)]);

        $this->osirisRepository->expects('exportCase')
            ->never();

        $this->caseExporter->export($case, $this->faker->randomElement(CaseExportType::cases()));

        Event::assertDispatched(CaseNotExportable::class);
    }

    /**
     * @throws CaseExportException
     */
    public function testDispatchCaseExportFailedEventWithRejectedFlag(): void
    {
        Event::fake();
        $case = $this->createCaseExportableToOsiris();

        $this->osirisRepository->expects('exportCase')
            ->andThrow(
                CaseExportRejectedException::fromErrorResponse(
                    $case,
                    new ErrorResponseException('test', ['test']),
                ),
            );

        $this->expectException(CaseExportRejectedException::class);

        $this->caseExporter->export($case, $this->faker->randomElement(CaseExportType::cases()));

        Event::assertDispatched(static function (CaseExportRejected $event) {
            return $event->errors === ['test'];
        });
    }

    /**
     * @throws CaseExportRejectedException
     */
    public function testDispatchCaseExportFailedEventForCaseExportException(): void
    {
        Event::fake();
        $case = $this->createCaseExportableToOsiris();

        $this->osirisRepository->expects('exportCase')
            ->andThrow(
                CaseExportException::fromThrowable(new Exception('test')),
            );

        $this->expectException(CaseExportException::class);

        $this->caseExporter->export($case, $this->faker->randomElement(CaseExportType::cases()));

        Event::assertDispatched(CaseExportFailed::class);
    }

    public function testPushingToOsirisQueue(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
                $index->gender = $this->faker->randomElement(Gender::all());
            }),
        ]);

        ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DEFINITIVE_ANSWERS);

        Queue::assertPushed(ExportCaseToOsiris::class);
    }

    public function testFeatureFlagValuePreventsPushingToQueue(): void
    {
        ConfigHelper::disableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $case = $this->createCaseExportableToOsiris();

        ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DEFINITIVE_ANSWERS);

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }
}
