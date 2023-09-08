<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Events\JobHandled;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Exceptions\Osiris\Client\ClientException;
use App\Exceptions\Osiris\Client\ErrorResponseException;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\SoapCaseExportRepository;
use App\Services\Osiris\OsirisCaseExportStrategy;
use App\Services\Osiris\SendDefinitiveAnswersStrategy;
use App\Services\Osiris\SendDeletedStatusStrategy;
use App\Services\Osiris\SendInitialAnswersStrategy;
use App\Services\Osiris\SoapMessage\QuestionnaireVersion;
use App\ValueObjects\OsirisNumber;
use Generator;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function config;

#[Group('osiris')]
final class ExportCaseToOsirisTest extends FeatureTestCase
{
    public function testItReleasesJobWhenAttemptsIsBelowLimit(): void
    {
        ConfigHelper::set('services.osiris.case_export_job.tries', 2);
        $case = $this->createCaseExportableToOsiris();

        $this->mock(SoapCaseExportRepository::class, static function (MockInterface $mock): void {
            $mock->expects('exportCase')
                ->andThrow(CaseExportException::fromThrowable(new ClientException('Osiris client error')));
        });

        $exportCaseToOsiris = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));

        $job = $this->createMock(Job::class);
        $job->expects($this->once())
            ->method('release');
        $job->expects($this->exactly(2))
            ->method('attempts')
            ->willReturn(1);

        $exportCaseToOsiris->setJob($job);

        $this->app->call([$exportCaseToOsiris, 'handle']);
    }

    public function testItDeletesJobWhenCaseExportIsRejected(): void
    {
        $case = $this->createCaseExportableToOsiris();

        $this->mock(SoapCaseExportRepository::class, function (MockInterface $mock) use ($case): void {
            $mock->expects('exportCase')
                ->andThrow(
                    CaseExportRejectedException::fromErrorResponse(
                        $case,
                        new ErrorResponseException($this->faker->word(), [$this->faker->sentence()]),
                    ),
                );
        });

        $job = $this->createMock(Job::class);
        $job->expects($this->once())->method('delete');
        $job->expects($this->atLeastOnce())->method('attempts')->willReturn(1);

        $exportCaseToOsiris = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));
        $exportCaseToOsiris->setJob($job);

        $this->app->call([$exportCaseToOsiris, 'handle']);
    }

    public function testItDeletesJobWhenLastAttemptFails(): void
    {
        $case = $this->createCaseExportableToOsiris();

        $this->mock(SoapCaseExportRepository::class, static function (MockInterface $mock): void {
            $mock->expects('exportCase')->andThrow(new RuntimeException('foo'));
        });

        $exportCaseToOsiris = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));
        $tries = $exportCaseToOsiris->tries;

        $job = $this->createMock(Job::class);
        $job->expects($this->once())->method('delete');
        $job->expects($this->atLeastOnce())->method('attempts')->willReturn($tries);

        $exportCaseToOsiris->setJob($job);

        $this->app->call([$exportCaseToOsiris, 'handle']);
    }

    public function testDispatchedEventOnSuccess(): void
    {
        $case = $this->createCaseExportableToOsiris();

        $this->mock(SoapCaseExportRepository::class, function (MockInterface $mock) use ($case): void {
            $mock->expects('exportCase')->andReturns(
                new CaseExportResult(
                    new OsirisNumber($this->faker->randomNumber(2)),
                    QuestionnaireVersion::V10->value,
                    'reportNumber',
                    $case->uuid,
                ),
            );
        });

        $job = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));
        $job->setJob($this->createMock(Job::class));

        Event::fake();

        $this->app->call([$job, 'handle']);

        Event::assertDispatched(JobHandled::class);
    }

    public function testDispatchedJobHandledEventOnFailure(): void
    {
        $case = $this->createCaseExportableToOsiris();

        $this->mock(SoapCaseExportRepository::class, function (MockInterface $mock) use ($case): void {
            $mock->expects('exportCase')
                ->andThrow(
                    CaseExportRejectedException::fromErrorResponse(
                        $case,
                        new ErrorResponseException($this->faker->word(), [$this->faker->word()]),
                    ),
                );
        });

        $job = new ExportCaseToOsiris($case->uuid, $this->faker->randomElement(CaseExportType::cases()));
        $job->setJob($this->createMock(Job::class));

        Event::fake();

        $this->app->call([$job, 'handle']);

        Event::assertDispatched(JobHandled::class);
    }

    public function testConstructJobWithCustomConfig(): void
    {
        $backoff = $this->faker->numberBetween(1, 10);
        $tries = $this->faker->numberBetween(1, 10);
        $timeout = $this->faker->numberBetween(1, 30);

        config()->set('services.osiris.case_export_job.backoff', $backoff);
        config()->set('services.osiris.case_export_job.tries', $tries);
        config()->set('services.osiris.case_export_job.timeout', $timeout);

        $job = new ExportCaseToOsiris($this->faker->uuid(), $this->faker->randomElement(CaseExportType::cases()));

        $this->assertEquals($backoff, $job->backoff);
        $this->assertEquals($tries, $job->tries);
        $this->assertEquals($timeout, $job->timeout);
    }

    public function testJobHoldsQueueConfiguration(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        ConfigHelper::set('services.osiris.case_export_job.queue_name', $queueName = $this->faker->word());
        ConfigHelper::set('services.osiris.case_export_job.connection', $connection = $this->faker->word());

        ExportCaseToOsiris::dispatchIfEnabled($this->faker->uuid(), $this->faker->randomElement(CaseExportType::cases()));

        Queue::assertPushed(function (ExportCaseToOsiris $job) use ($queueName, $connection): bool {
            $this->assertEquals($queueName, $job->queue);
            $this->assertEquals($connection, $job->connection);
            return true;
        });
    }

    /**
     * @param class-string<OsirisCaseExportStrategy> $strategyClass
     */
    #[DataProvider('provideCaseExportTypesWithCorrespondingStrategy')]
    public function testAppInjectsStrategyBasedOnCaseExportType(
        CaseExportType $caseExportType,
        string $strategyClass,
    ): void {
        Event::fake();

        $this->assertCount(3, CaseExportType::cases(), 'Test needs to be updated when new case export type is added');

        $mockClass = match ($strategyClass) {
            SendInitialAnswersStrategy::class => new class implements OsirisCaseExportStrategy {
                public int $executed = 0;

                public function execute(string $caseUuid): void
                {
                    $this->executed++;
                }

                public function supports(CaseExportType $caseExportType): bool
                {
                    return $caseExportType === CaseExportType::INITIAL_ANSWERS;
                }
            },
            SendDefinitiveAnswersStrategy::class => new class implements OsirisCaseExportStrategy {
                public int $executed = 0;

                public function execute(string $caseUuid): void
                {
                    $this->executed++;
                }

                public function supports(CaseExportType $caseExportType): bool
                {
                    return $caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
                }
            },
            SendDeletedStatusStrategy::class => new class implements OsirisCaseExportStrategy {
                public int $executed = 0;

                public function execute(string $caseUuid): void
                {
                    $this->executed++;
                }

                public function supports(CaseExportType $caseExportType): bool
                {
                    return $caseExportType === CaseExportType::DELETED_STATUS;
                }
            },
        };

        $this->app->instance($strategyClass, $mockClass);

        $job = new ExportCaseToOsiris($this->faker->uuid(), $caseExportType);
        $job->setJob(Mockery::mock(Job::class));

        $this->app->call([$job, 'handle']);

        $this->assertEquals(1, $mockClass->executed);
    }

    public static function provideCaseExportTypesWithCorrespondingStrategy(): Generator
    {
        yield 'with case export type `initial`' => [CaseExportType::INITIAL_ANSWERS, SendInitialAnswersStrategy::class];
        yield 'with case export type `definitive`' => [CaseExportType::DEFINITIVE_ANSWERS, SendDefinitiveAnswersStrategy::class];
        yield 'with case export type `deleted`' => [CaseExportType::DELETED_STATUS, SendDeletedStatusStrategy::class];
    }
}
