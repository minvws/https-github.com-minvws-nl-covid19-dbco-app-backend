<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\Calendar\StrategyLoader;

use App\Dto\Admin\CreateDateOperationDto;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Models\Policy\PolicyVersion;
use App\Services\Policy\Calendar\StrategyLoader\AbstractDateOperationStrategyLoader;
use MinVWS\DBCO\Enum\Models\Enum;

use function array_map;

trait AbstractDateOperationStrategyLoaderHelper
{
    public function itCanGetDateOperations(AbstractDateOperationStrategyLoader $strategyLoader): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $calendarItemConfigOne = CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->period())
            ->withStrategies(withDateOperation: true, countDateOperation: 3)
            ->create();
        CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->point())
            ->withStrategies(withDateOperation: true)
            ->create();

        $strategyOne = $calendarItemConfigOne->calendarItemConfigStrategies->first()->loadMissing('dateOperations');

        $dateOperations = $strategyLoader->get($strategyOne);

        $this->assertEqualsCanonicalizing(
            $dateOperations
                ->map($this->castDateOperationToPrimitives(...))
                ->toArray(),
            $strategyOne
                ->dateOperations
                ->map($this->castDateOperationToPrimitives(...))
                ->toArray(),
        );
    }

    public function itCanDeleteDateOperations(AbstractDateOperationStrategyLoader $strategyLoader): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $calendarItemConfigs = CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->period())
            ->withStrategies(withDateOperation: true, countDateOperation: 2)
            ->count(2)
            ->create();
        CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->point())
            ->withStrategies(withDateOperation: true, countDateOperation: 2)
            ->create();

        $this->assertDatabaseCount(DateOperation::class, 10);

        $strategeyOne = $calendarItemConfigs->first()->calendarItemConfigStrategies->first();

        $strategyLoader->deleteAll($strategeyOne);

        $this->assertDatabaseCount(DateOperation::class, 8);
        $this->assertDatabaseMissing(
            DateOperation::class,
            ['calendar_item_config_strategy_uuid' => $strategeyOne->uuid],
        );
    }

    public function itCanSetDateOperations(AbstractDateOperationStrategyLoader $strategyLoader): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->point())
            ->withStrategies(withDateOperation: true, countDateOperation: 2)
            ->create();

        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->create();

        $dateOperationDtos = DateOperation::factory()
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

        $this->assertDatabaseCount(DateOperation::class, 2);

        $dateOperationsTwo = $strategyLoader->set($calendarItemConfigStrategy, $dateOperationDtos);

        $this->assertCount(2, $dateOperationsTwo);
        $this->assertDatabaseCount(DateOperation::class, 4);

        $dateOperationDtos->each(function (CreateDateOperationDto $dateOperationDto): void {
            $this->assertDatabaseHas(DateOperation::class, $dateOperationDto->toEloquentAttributes());
        });
    }

    private function castDateOperationToPrimitives(DateOperation $dateOperation): array
    {
        return array_map(
            static fn (mixed $attribute): mixed => $attribute instanceof Enum ? $attribute->value : $attribute,
            $dateOperation->toArray(),
        );
    }
}
