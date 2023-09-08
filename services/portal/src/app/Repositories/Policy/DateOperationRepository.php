<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\CreateDateOperationDto;
use App\Dto\Admin\UpdateDateOperationDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use RuntimeException;

use function array_merge;
use function sprintf;

final class DateOperationRepository
{
    /**
     * @return Collection<array-key,DateOperation>
     */
    public function getDateOperations(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection
    {
        return DateOperation::query()
            ->where('calendar_item_config_strategy_uuid', $calendarItemConfigStrategy->uuid)
            ->get();
    }

    public function deleteAllDateOperations(CalendarItemConfigStrategy $calendarItemConfigStrategy): int
    {
        /** @var int */
        return DateOperation::query()
            ->where('calendar_item_config_strategy_uuid', $calendarItemConfigStrategy->uuid)
            ->delete();
    }

    /**
     * @param array<CreateDateOperationDto>|Collection<CreateDateOperationDto> $dtos
     *
     * @return EloquentCollection<array-key,DateOperation>
     *
     * @note The reason we loop over the dto's and use Eloquent create() over insert(), is because insert() does not
     * emit Eloquent events and does not fill the model with the default values (i.e. timestamps)
     */
    public function insertDateOperationDtosByCalendarItemConfig(CalendarItemConfigStrategy $calendarItemConfigStrategy, array|Collection $dtos): EloquentCollection
    {
        /** @var EloquentCollection<DateOperation> */
        return LazyCollection::wrap($dtos)
            ->map(static fn (CreateDateOperationDto $dto): array => $dto->toEloquentAttributes())
            ->map(static fn (array $attributes): array
                => array_merge($attributes, ['calendar_item_config_strategy_uuid' => $calendarItemConfigStrategy->uuid]))
            ->map(static fn (array $attributes): DateOperation => DateOperation::query()->create($attributes))
            ->pipeInto(EloquentCollection::class);
    }

    public function updateDateOperation(DateOperation $dateOperation, UpdateDateOperationDto $dto): DateOperation
    {
        if (!$dateOperation->update($dto->toEloquentAttributes())) {
            throw new RuntimeException(
                sprintf('Failed to update Date Operation with UUID: "%s"', $dateOperation->uuid),
            );
        }

        return $dateOperation;
    }
}
