<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\UpdateCaseIndexAge;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Schema\Types\SchemaType;
use Carbon\CarbonImmutable;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Psr\Log\LoggerInterface;
use Tests\Feature\FeatureTestCase;

class UpdateCaseIndexAgeTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake([
            JobProcessed::class,
        ]);
    }

    public function testDispatchJobWithKnownUuidLogsDebug(): void
    {
        $case = $this->createCaseWithIndex();

        $this->mock(LoggerInterface::class)
            ->shouldReceive('debug')
            ->withSomeOfArgs('Case index-age updated')
            ->once();

        Bus::dispatch(new UpdateCaseIndexAge($case->uuid));
    }

    public function testDispatchJobWithUnknownUuidLogsDebug(): void
    {
        $this->mock(LoggerInterface::class)
            ->shouldReceive('debug')
            ->withSomeOfArgs('Case not found')
            ->once();

        Bus::dispatch(new UpdateCaseIndexAge($this->faker->uuid));
    }

    private function createCaseWithIndex(): EloquentCase
    {
        $indexSchemaVersion = EloquentCase::getSchema()
            ->getCurrentVersion()
            ->getField('index')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        return $this->createCase([
            'index' => Index::newInstanceWithVersion($indexSchemaVersion, function (Index $index): void {
                $index->dateOfBirth = CarbonImmutable::parse($this->faker->date());
            }),
            'bco_status' => BCOStatus::open(),
        ]);
    }
}
