<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\DbCaseStatusRepository;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use Tests\Feature\FeatureTestCase;

class DbCaseStatusRepositoryTest extends FeatureTestCase
{
    private DbCaseStatusRepository $dbCaseStatusRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbCaseStatusRepository = $this->app->get(DbCaseStatusRepository::class);
    }

    public function testUpdateTimeoutIndexStatusLimit(): void
    {
        $case1 = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'pairing_expires_at' => $this->faker->dateTimeThisMonth(),
            'index_status' => IndexStatus::paired(),
            'updated_at' => CarbonImmutable::now(),
        ]);
        $case2 = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'pairing_expires_at' => $this->faker->dateTimeThisMonth(),
            'index_status' => IndexStatus::paired(),
            'updated_at' => CarbonImmutable::now()->subHour(),
        ]);

        $this->dbCaseStatusRepository->updateTimeoutIndexStatus(1);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case1->uuid,
            'index_status' => IndexStatus::timeout()->value, // updated
        ]);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case2->uuid,
            'index_status' => IndexStatus::paired()->value, // not updated
        ]);
    }

    public function testUpdateExpiredIndexStatusLimit(): void
    {
        $case1 = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::paired(),
            'index_submitted_at' => null,
            'window_expires_at' => $this->faker->dateTimeThisMonth(),
            'updated_at' => CarbonImmutable::now(),
        ]);
        $case2 = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::paired(),
            'index_submitted_at' => null,
            'window_expires_at' => $this->faker->dateTimeThisMonth(),
            'updated_at' => CarbonImmutable::now()->subHour(),
        ]);

        $this->dbCaseStatusRepository->updateExpiredIndexStatus(1);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case1->uuid,
            'index_status' => IndexStatus::expired()->value, // updated
        ]);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case2->uuid,
            'index_status' => IndexStatus::paired()->value, // not updated
        ]);
    }
}
