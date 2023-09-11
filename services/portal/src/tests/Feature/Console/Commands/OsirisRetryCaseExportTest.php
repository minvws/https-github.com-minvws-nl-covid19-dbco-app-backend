<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase\Index;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\StatusIndexContactTracing;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
use MinVWS\DBCO\Enum\Models\Gender;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Command\Command;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

#[Group('osiris-send')]
#[Group('osiris')]
final class OsirisRetryCaseExportTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
    }

    public function testRetryExportIfFeatureDisabled(): void
    {
        ConfigHelper::disableFeatureFlag('osiris_retry_case_export_enabled');

        $this->artisan('osiris:case-export:retry')
            ->expectsOutput('Osiris retry case-export is disabled')
            ->assertSuccessful();
    }

    public function testRetryExportingOverdueNotification(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_retry_case_export_enabled');
        Queue::fake();

        $now = CarbonImmutable::now();
        $notifiedAt = $this->faker->dateTimeBetween('-2 hours', $now);
        $caseWithOverdueNotification = $this->createCase([
            'created_at' => $now,
            'updated_at' => $now,
            'status_index_contact_tracing' => StatusIndexContactTracing::COMPLETED(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
                $index->gender = $this->faker->randomElement(Gender::all());
            }),
            'hpzone_number' => null,
        ]);
        $this->createOsirisNotificationForCase($caseWithOverdueNotification, ['notified_at' => $notifiedAt]);

        $artisan = $this->artisan('osiris:case-export:retry');

        $this->assertEquals(Command::SUCCESS, $artisan->execute());

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($caseWithOverdueNotification) {
            return $job->caseUuid === $caseWithOverdueNotification->uuid
                && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
        });
    }

    public function testRetryExportingOverdueNotificationForOpenCaseShouldNotExportNotification(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_retry_case_export_enabled');
        Queue::fake();

        $now = CarbonImmutable::now();
        $notifiedAt = $this->faker->dateTimeBetween('-2 hours', $now);
        // Create case with an overdue osiris notification
        $this->createCaseAndOsirisNotification([
            'created_at' => $now,
            'updated_at' => $now,
            'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
            'hpzone_number' => null,
        ], [
            'notified_at' => $notifiedAt,
        ]);

        $artisan = $this->artisan('osiris:case-export:retry');

        $this->assertEquals(Command::SUCCESS, $artisan->execute());

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testRetryExportingCaseWithoutNotification(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_retry_case_export_enabled');
        Queue::fake();

        $caseWithoutNotification = $this->createCaseExportableToOsiris([
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'status_index_contact_tracing' => StatusIndexContactTracing::NEW(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
                $index->gender = $this->faker->randomElement(Gender::all());
            }),
        ]);

        $artisan = $this->artisan('osiris:case-export:retry');

        $this->assertEquals(Command::SUCCESS, $artisan->execute());

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($caseWithoutNotification) {
            return $job->caseUuid === $caseWithoutNotification->uuid
                && $job->caseExportType === CaseExportType::INITIAL_ANSWERS;
        });
    }

    public function testRetryExportingNotificationNotNeeded(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_retry_case_export_enabled');
        Queue::fake();

        $now = CarbonImmutable::now();
        $this->createCaseAndOsirisNotification([
            'created_at' => $now,
            'updated_at' => $now,
            'hpzone_number' => null,
        ]);

        $artisan = $this->artisan('osiris:case-export:retry');

        $this->assertEquals(Command::SUCCESS, $artisan->execute());

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }
}
