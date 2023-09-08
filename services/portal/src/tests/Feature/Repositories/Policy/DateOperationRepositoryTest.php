<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Dto\Admin\CreateDateOperationDto;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\DateOperationRepository;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('dateOperation')]
final class DateOperationRepositoryTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testItCanBeInitialized(): void
    {
        $repository = $this->app->make(DateOperationRepository::class);

        $this->assertInstanceOf(DateOperationRepository::class, $repository);
    }

    public function testGetDateOperations(): void
    {
        /** @var DateOperationRepository $repository */
        $repository = $this->app->make(DateOperationRepository::class);

        $policyVersionOne = PolicyVersion::factory()->create();
        $calendarItemConfigOne = CalendarItemConfig::factory()
            ->recycle($policyVersionOne)
            ->for(CalendarItem::factory()->period())
            ->withStrategies(withDateOperation: true)
            ->count(2)
            ->create();

        $policyVersionTwo = PolicyVersion::factory()->create();
        CalendarItemConfig::factory()
            ->recycle($policyVersionTwo)
            ->for(CalendarItem::factory()->point())
            ->withStrategies(withDateOperation: true)
            ->count(2)
            ->create();

        $this->assertDatabaseCount(DateOperation::class, 6);

        $strategyOne = $calendarItemConfigOne->first()->calendarItemConfigStrategies->first();

        $result = $repository->getDateOperations($strategyOne);

        $this->assertEqualsCanonicalizing($strategyOne->dateOperations()->pluck('uuid')->toArray(), $result->pluck('uuid')->toArray());
    }

    public function testDeleteAllDateOperations(): void
    {
        /** @var DateOperationRepository $repository */
        $repository = $this->app->make(DateOperationRepository::class);

        $policyVersionOne = PolicyVersion::factory()->create();
        $calendarItemConfigOne = CalendarItemConfig::factory()
            ->recycle($policyVersionOne)
            ->for(CalendarItem::factory()->period())
            ->withStrategies(withDateOperation: true)
            ->count(2)
            ->create();

        $policyVersionTwo = PolicyVersion::factory()->create();
        CalendarItemConfig::factory()
            ->recycle($policyVersionTwo)
            ->for(CalendarItem::factory()->point())
            ->withStrategies(withDateOperation: true)
            ->count(2)
            ->create();

        $strategyOne = $calendarItemConfigOne->first()->calendarItemConfigStrategies->first();

        // Add 4 more date operations to the strategy (total of 5)
        DateOperation::factory()
            ->recycle($strategyOne)
            ->count(4)
            ->create();

        $this->assertDatabaseCount(DateOperation::class, 10);

        $strategyOne = $calendarItemConfigOne->first()->calendarItemConfigStrategies->first();

        $result = $repository->deleteAllDateOperations($strategyOne);

        $this->assertSame(5, $result);
        $this->assertDatabaseCount(DateOperation::class, 5);
        $strategyOne
            ->dateOperations
            ->each(function (DateOperation $dateOpreation): void {
                $this->assertDatabaseMissing(DateOperation::class, ['uuid' => $dateOpreation->uuid]);
            });
    }

    public function testInsertDateOperationDtosByCalendarItemConfig(): void
    {
        /** @var DateOperationRepository $repository */
        $repository = $this->app->make(DateOperationRepository::class);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()
            ->recycle($policyVersion)
            ->for(CalendarItemConfig::factory())
            ->create();
        $dateOperationDtos = DateOperation::factory()
            ->recycle($calendarItemConfigStrategy)
            ->count(2)
            ->make()
            ->map(static fn (DateOperation $dateOperation): CreateDateOperationDto
                => new CreateDateOperationDto(
                    identifier: $dateOperation->identifier_type,
                    mutation: $dateOperation->mutation_type,
                    amount: $dateOperation->amount,
                    unitOfTime: $dateOperation->unit_of_time_type,
                    originDate: $dateOperation->origin_date_type,
                ));

        $this->assertDatabaseCount(DateOperation::class, 0);

        $result = $repository->insertDateOperationDtosByCalendarItemConfig($calendarItemConfigStrategy, $dateOperationDtos);

        $this->assertCount(2, $result);
        $this->assertDatabaseCount(DateOperation::class, 2);

        $dateOperationDtos->each(function (CreateDateOperationDto $dateOperationDto): void {
            // check if we can find the dto's data in the database
            $this->assertDatabaseHas(DateOperation::class, $dateOperationDto->toEloquentAttributes());
        });

        $result->each(function (DateOperation $dateOperation) use ($dateOperationDtos): void {
            // check if we can find the returned uuids in the database
            $this->assertDatabaseHas(DateOperation::class, ['uuid' => $dateOperation->uuid]);

            // check if we can find the dto's data in the result
            $first = $dateOperationDtos->first(static function (CreateDateOperationDto $dateOperationDto) use ($dateOperation) {
                return $dateOperationDto->identifier === $dateOperation->identifier_type
                    && $dateOperationDto->mutation === $dateOperation->mutation_type
                    && $dateOperationDto->amount === $dateOperation->amount
                    && $dateOperationDto->unitOfTime === $dateOperation->unit_of_time_type
                    && $dateOperationDto->originDate === $dateOperation->origin_date_type;
            });

            $this->assertNotNull($first, sprintf('Could not find dto in results'));
        });
    }
}
