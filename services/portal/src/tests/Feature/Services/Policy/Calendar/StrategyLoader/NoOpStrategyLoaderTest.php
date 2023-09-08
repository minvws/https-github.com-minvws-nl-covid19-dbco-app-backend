<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\Calendar\StrategyLoader;

use App\Dto\Admin\CreateDateOperationDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Services\Policy\Calendar\StrategyLoader\NoOpStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarStrategyLoader')]
final class NoOpStrategyLoaderTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testItCanBeInitialized(): void
    {
        $strategyLoader = $this->app->make(NoOpStrategyLoader::class);

        $this->assertInstanceOf(NoOpStrategyLoader::class, $strategyLoader);
        $this->assertInstanceOf(StrategyLoader::class, $strategyLoader);
    }

    public function testItCanInitializeCalendarItemConfig(): void
    {
        /** @var NoOpStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(NoOpStrategyLoader::class);

        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->create();

        $dateOperations = $strategyLoader->getInitializeData($calendarItemConfigStrategy);

        $this->assertCount(0, $dateOperations);
    }

    public function testItCanGetDateOperations(): void
    {
        /** @var NoOpStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(NoOpStrategyLoader::class);

        $calendarItemConfigStrategyOne = CalendarItemConfigStrategy::factory()->create();
        DateOperation::factory()->recycle($calendarItemConfigStrategyOne)->create();

        $calendarItemConfigStrategyTwo = CalendarItemConfigStrategy::factory()->create();
        DateOperation::factory()->recycle($calendarItemConfigStrategyTwo)->create();

        $this->assertDatabaseCount(DateOperation::class, 2);

        $dateOperations = $strategyLoader->get($calendarItemConfigStrategyOne);

        $this->assertCount(0, $dateOperations);
    }

    public function testItCanDeleteDateOperations(): void
    {
        /** @var NoOpStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(NoOpStrategyLoader::class);

        $calendarItemConfigStrategyOne = CalendarItemConfigStrategy::factory()->create();
        $dateOperationUuidOne = DateOperation::factory()
            ->recycle($calendarItemConfigStrategyOne)
            ->createOne()
            ->uuid;

        $calendarItemConfigStrategyTwo = CalendarItemConfigStrategy::factory()->create();
        $dateOperationUuidTwo = DateOperation::factory()
            ->recycle($calendarItemConfigStrategyTwo)
            ->createOne()
            ->uuid;

        $this->assertDatabaseCount(DateOperation::class, 2);

        $strategyLoader->deleteAll($calendarItemConfigStrategyOne);

        $this->assertDatabaseCount(DateOperation::class, 2);
        $this->assertDatabaseHas(DateOperation::class, ['uuid' => $dateOperationUuidOne]);
        $this->assertDatabaseHas(DateOperation::class, ['uuid' => $dateOperationUuidTwo]);
    }

    public function testItCanSetDateOperations(): void
    {
        /** @var NoOpStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(NoOpStrategyLoader::class);

        $calendarItemConfigStrategyOne = CalendarItemConfigStrategy::factory()->create();
        DateOperation::factory()->recycle($calendarItemConfigStrategyOne)->create();

        $calendarItemConfigStrategyTwo = CalendarItemConfigStrategy::factory()->create();
        $dateOperation = DateOperation::factory()->recycle($calendarItemConfigStrategyTwo)->create();

        $dateOperationDtos = Collection::make([$dateOperation])
            ->map(static fn (DateOperation $dateOperation): CreateDateOperationDto
                => new CreateDateOperationDto(
                    identifier: $dateOperation->identifier_type,
                    mutation: $dateOperation->mutation_type,
                    amount: $dateOperation->amount + 99,
                    unitOfTime: $dateOperation->unit_of_time_type,
                    originDate: $dateOperation->origin_date_type,
                ));

        $this->assertDatabaseCount(DateOperation::class, 2);

        $dateOperationsTwo = $strategyLoader->set($calendarItemConfigStrategyTwo, $dateOperationDtos);

        $this->assertCount(0, $dateOperationsTwo);
        $this->assertDatabaseCount(DateOperation::class, 2);
        $this->assertDatabaseHas(DateOperation::class, ['uuid' => $dateOperation->uuid, 'amount' => $dateOperation->amount]);
    }
}
