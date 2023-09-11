<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Services\CaseStatusService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('update-status')]
class CaseStatusCommandTest extends FeatureTestCase
{
    public function testExpiredCaseStatusUpdate(): void
    {
        $expiredCase = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::paired(),
            'window_expires_at' => CarbonImmutable::now()->subMinutes(1),
        ]);
        $artisan = $this->artisan('cases:update-status');
        $artisan->assertExitCode(0)
            ->execute();

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $expiredCase->uuid,
            'bco_status' => BCOStatus::open()->value,
            'index_status' => IndexStatus::expired()->value,
        ]);
        $this->assertDatabaseHas('event', [
            'type' => IndexStatus::expired()->value,
        ]);
    }

    public function testTimeoutCaseStatusUpdates(): void
    {
        $timoutCase = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::initial(),
            'pairing_expires_at' => CarbonImmutable::now()->subMinutes(1),
        ]);
        $artisan = $this->artisan('cases:update-status');
        $artisan->assertExitCode(0)
            ->execute();

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $timoutCase->uuid,
            'bco_status' => BCOStatus::open()->value,
            'index_status' => IndexStatus::timeout()->value,
        ]);
    }

    public function testLimitOption(): void
    {
        $limit = $this->faker->randomNumber();

        $this->mock(CaseStatusService::class, static function (MockInterface $mock) use ($limit): void {
            $mock->expects('updateAllTimeSensitiveStatus')
                ->with($limit);
        });


        $artisan = $this->artisan(sprintf('cases:update-status --limit=%s', $limit));
        $artisan->assertExitCode(0)
            ->execute();
    }
}
