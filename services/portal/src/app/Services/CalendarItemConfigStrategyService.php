<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Admin\CreateDateOperationDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Services\Policy\Calendar\StrategyLoader\DateOperationStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoaderFactory;
use Generator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use Webmozart\Assert\Assert;

use function is_null;

final class CalendarItemConfigStrategyService
{
    public function __construct(
        private readonly StrategyLoaderFactory $strategyLoaderFactory,
        private readonly ConnectionInterface $db,
    )
    {
    }

    public function initializeCalendarItemConfigStrategies(CalendarItemConfigStrategy $calendarItemConfigStrategy): void
    {
        $strategyLoader = $this->strategyLoaderFactory->make($calendarItemConfigStrategy->strategy_type);

        $this->db->transaction(static function () use ($calendarItemConfigStrategy, $strategyLoader): void {
            $strategyLoader->set(
                $calendarItemConfigStrategy,
                $strategyLoader->getInitializeData($calendarItemConfigStrategy),
            );
        });
    }

    public function updateCalendarItemConfigStrategies(CalendarItemConfigStrategy $calendarItemConfigStrategy): void
    {
        $this->db->transaction(function () use ($calendarItemConfigStrategy): void {
            $oldStrategyLoader = $this->getOldStrategyLoader($calendarItemConfigStrategy);
            $newStrategyLoader = $this->getNewStrategyLoader($calendarItemConfigStrategy);

            $keepDefaultDateOperation = $this->onDateOperationStrategyLoadersKeepDefault(
                $calendarItemConfigStrategy,
                $oldStrategyLoader,
                $newStrategyLoader,
            );
            // prime the generator so everything is executed up to the f
            $keepDefaultDateOperation->current();

            $oldStrategyLoader->deleteAll($calendarItemConfigStrategy);

            $newDateMutations = $newStrategyLoader->getInitializeData($calendarItemConfigStrategy);
            $keepDefaultDateOperation->send($newDateMutations);

            $newStrategyLoader->set($calendarItemConfigStrategy, $keepDefaultDateOperation->getReturn());
        });
    }

    private function getOldStrategyLoader(CalendarItemConfigStrategy $calendarItemConfigStrategy): StrategyLoader
    {
        /** @var PointCalendarStrategyType|PeriodCalendarStrategyType $oldStrategyType */
        $oldStrategyType = $calendarItemConfigStrategy->getOriginal('strategy_type');

        return $this->strategyLoaderFactory->make($oldStrategyType);
    }

    private function getNewStrategyLoader(CalendarItemConfigStrategy $calendarItemConfigStrategy): StrategyLoader
    {
        /** @var PointCalendarStrategyType|PeriodCalendarStrategyType $newStrategyType */
        $newStrategyType = $calendarItemConfigStrategy->strategy_type;

        return $this->strategyLoaderFactory->make($newStrategyType);
    }

    /**
     * @return Generator<int,null,Collection<array-key,mixed>,Collection<array-key,mixed>>
     */
    private function onDateOperationStrategyLoadersKeepDefault(
        CalendarItemConfigStrategy $calendarItemConfigStrategy,
        StrategyLoader $oldStrategyLoader,
        StrategyLoader $newStrategyLoader,
    ): Generator {
        $oldDefaultDateOperation = null;
        if ($oldStrategyLoader instanceof DateOperationStrategyLoader) {
            $oldDefaultDateOperation = $oldStrategyLoader
                ->get($calendarItemConfigStrategy)
                ->first(static fn (DateOperation $dateOperation): bool
                    => $dateOperation->identifier_type === DateOperationIdentifier::default());
        }

        /** @var ?DateOperation $oldDefaultDateOperation */
        Assert::nullOrIsInstanceOf($oldDefaultDateOperation, DateOperation::class);

        $dateMutations = yield;

        // @codeCoverageIgnoreStart
        if (!$newStrategyLoader instanceof DateOperationStrategyLoader) {
             /**
              * All current implementations are an instance of DateOperationStrategyLoader. This is added for feature
              * cases where a non-DateOperationStrategyLoader implementation might exist.
              */
            return $dateMutations;
        }
        // @codeCoverageIgnoreEnd

        Assert::isInstanceOf($dateMutations, Collection::class);
        Assert::allIsInstanceOf($dateMutations, CreateDateOperationDto::class);

        // @codeCoverageIgnoreStart
        if (is_null($oldDefaultDateOperation)) {
            /**
             * All current implementations are an instance of DateOperationStrategyLoader. This is added for feature
             * cases where a non-DateOperationStrategyLoader implementation might exist.
             */
            return $dateMutations;
        }
        // @codeCoverageIgnoreEnd

        $newDefaultDto = new CreateDateOperationDto(
            identifier: DateOperationIdentifier::default(),
            mutation: $oldDefaultDateOperation->mutation_type,
            amount: $oldDefaultDateOperation->amount,
            unitOfTime: $oldDefaultDateOperation->unit_of_time_type,
            originDate: $oldDefaultDateOperation->origin_date_type,
        );

        return $dateMutations
            ->reject(static fn (CreateDateOperationDto $dto): bool => $dto->identifier === DateOperationIdentifier::default())
            ->add($newDefaultDto)
            ->values();
    }
}
