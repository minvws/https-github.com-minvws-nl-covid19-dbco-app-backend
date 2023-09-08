<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Repositories\CaseStatusRepository;
use App\Services\CaseStatusService;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Tests\Feature\FeatureTestCase;

class CaseStatusServiceTest extends FeatureTestCase
{
    public function testUpdateAllTimeSensitiveStatus(): void
    {
        $limit = $this->faker->randomDigit();

        $this->mock(CaseStatusRepository::class, function (MockInterface $mock) use ($limit): void {
            $mock->expects('updateTimeoutIndexStatus')
                ->with($limit)
                ->andReturn($this->faker->randomDigit());

            $mock->expects('updateExpiredIndexStatus')
                ->with($limit)
                ->andReturn(new Collection());
        });

        $caseStatusService = $this->app->get(CaseStatusService::class);
        $caseStatusService->updateAllTimeSensitiveStatus($limit);
    }

    public function testUpdateTimeoutIndexStatusLogsWarningWhenLimitReached(): void
    {
        $this->createCase([
            'bco_status' => BCOStatus::open(),
            'pairing_expires_at' => $this->faker->dateTimeThisMonth(),
            'index_status' => IndexStatus::paired(),
        ]);

        $logger = $this->spy(LoggerInterface::class);
        $logger->expects('warning')
            ->once()
            ->with('Number of affected rows is equal to limit for updateTimeoutIndexStatus');

        $caseStatusService = $this->app->get(CaseStatusService::class);
        $caseStatusService->updateAllTimeSensitiveStatus(1);
    }

    public function testUpdateExpiredIndexStatusLogsWarningWhenLimitReached(): void
    {
        $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::paired(),
            'index_submitted_at' => null,
            'window_expires_at' => $this->faker->dateTimeThisMonth(),
        ]);

        $logger = $this->spy(LoggerInterface::class);
        $logger->expects('warning')
            ->once()
            ->with('Number of affected rows is equal to limit for updateExpiredIndexStatus');

        $caseStatusService = $this->app->get(CaseStatusService::class);
        $caseStatusService->updateAllTimeSensitiveStatus(1);
    }
}
