<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase\Index;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\StatusIndexContactTracing;
use App\Repositories\CaseOsirisNotificationRepository;
use App\Services\Osiris\CaseExportRetryService;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function collect;

#[Group('osiris')]
#[Group('osiris-notification')]
final class CaseExportRetryServiceTest extends FeatureTestCase
{
    private CaseOsirisNotificationRepository $caseOsirisNotificationRepository;
    private CaseExportRetryService $caseExportRetryService;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $this->caseOsirisNotificationRepository = Mockery::mock(CaseOsirisNotificationRepository::class);
        $this->caseExportRetryService = new CaseExportRetryService($this->caseOsirisNotificationRepository);
    }

    public function testResendingCasesWithOlderNotifications(): void
    {
        ConfigHelper::set(
            'services.osiris.retry_from_date',
            $this->faker->dateTimeBetween('-2 weeks', '-1 week')->format('Y-m-d H:i:s'),
        );

        $caseWithOlderOsirisNotification = $this->createCaseAndOsirisNotification([
            'created_at' => $this->faker->dateTimeBetween('-2 days', '-1 day'),
            'updated_at' => CarbonImmutable::now(),
            'status_index_contact_tracing' => StatusIndexContactTracing::COMPLETED(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
            'hpzone_number' => null,
        ], [
            'notified_at' => $this->faker->dateTimeBetween('-2 hours', '-1 hour'),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
        ]);

        /** @var CaseExportRetryService $caseExportRetryService */
        $caseExportRetryService = $this->app->make(CaseExportRetryService::class);
        $caseExportRetryService->exportOverdueCases();

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($caseWithOlderOsirisNotification) {
            return $job->caseUuid === $caseWithOlderOsirisNotification->uuid
                && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
        });
    }

    public function testResendingCasesWithoutNotifications(): void
    {
        $createdAt = $this->faker->dateTimeBetween('-2 hours');
        $caseWithoutNotification = $this->createCaseExportableToOsiris([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
        ]);

        $caseExportRetryService = $this->app->make(CaseExportRetryService::class);
        $caseExportRetryService->exportOverdueCases();

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($caseWithoutNotification) {
            return $job->caseUuid === $caseWithoutNotification->uuid
                && $job->caseExportType === CaseExportType::INITIAL_ANSWERS;
        });
    }

    public function testExportOverdueCasesSkippedWhenCaseHasHpzoneNumber(): void
    {
        $createdAt = $this->faker->dateTimeBetween('-2 hours');
        $this->createCase([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
            'hpzone_number' => (string) $this->faker->unique()->numberBetween(1_000_000, 9_999_999),
        ]);

        $caseExportRetryService = $this->app->make(CaseExportRetryService::class);
        $caseExportRetryService->exportOverdueCases();

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testExportOverdueCasesExcludesDeletedCasesWithNoNotification(): void
    {
        $case = $this->createCaseExportableToOsiris();

        $this->caseOsirisNotificationRepository->expects('getUpdatedCasesWithoutRecentOsirisNotification')
            ->andReturn(collect([]));

        $this->caseOsirisNotificationRepository->expects('findRetryableDeletedCases')
            ->andReturn(collect([$case]));

        $this->caseExportRetryService->exportOverdueCases();

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testExportOverdueCasesExcludesDeletedCasesWithNewerDeletionNotification(): void
    {
        $deletedAt = CarbonImmutable::parse($this->faker->dateTimeBetween('-1 week', '-1 day'));
        $case = $this->createCaseExportableToOsiris(['deleted_at' => $deletedAt]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $deletedAt->subDay(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
        ]);
        $notification = $this->createOsirisNotificationForCase($case, [
            'notified_at' => $deletedAt->addMinute(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
        ]);

        $this->caseOsirisNotificationRepository->expects('getUpdatedCasesWithoutRecentOsirisNotification')
            ->andReturn(collect([]));

        $this->caseOsirisNotificationRepository->expects('findRetryableDeletedCases')
            ->andReturn(collect([$case]));

        $this->caseOsirisNotificationRepository->expects('findLatestDeletedStatusNotification')
            ->andReturn($notification);

        $this->caseExportRetryService->exportOverdueCases();

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testExportOverdueCasesIncludesDeletedCasesWithOlderDeletionNotification(): void
    {
        $deletedAt = CarbonImmutable::parse($this->faker->dateTimeBetween('-1 week', '-1 day'));
        $case = $this->createCaseExportableToOsiris(['deleted_at' => $deletedAt]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $deletedAt->subDay(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
        ]);
        $notification = $this->createOsirisNotificationForCase($case, [
            'notified_at' => $deletedAt->subMinute(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DELETED,
        ]);

        $this->caseOsirisNotificationRepository->expects('getUpdatedCasesWithoutRecentOsirisNotification')
            ->andReturn(collect([]));

        $this->caseOsirisNotificationRepository->expects('findRetryableDeletedCases')
            ->andReturn(collect([$case]));

        $this->caseOsirisNotificationRepository->expects('findLatestDeletedStatusNotification')
            ->andReturn($notification);

        $this->caseExportRetryService->exportOverdueCases();

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid
                && $job->caseExportType === CaseExportType::DELETED_STATUS;
        });
    }
}
