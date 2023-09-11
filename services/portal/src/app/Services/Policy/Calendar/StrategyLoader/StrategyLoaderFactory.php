<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use Illuminate\Contracts\Foundation\Application;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use RuntimeException;

final class StrategyLoaderFactory
{
    public function __construct(private readonly Application $app)
    {
    }

    public function make(null|PointCalendarStrategyType|PeriodCalendarStrategyType $strategyLoader): StrategyLoader
    {
        return match ($strategyLoader) {
            null => $this->app->make(NoOpStrategyLoader::class),

            PointCalendarStrategyType::fixedStrategy(),
            PeriodCalendarStrategyType::fixedStrategy()
                => $this->app->make(FixedStrategyLoader::class),

            PointCalendarStrategyType::flexStrategy(),
            PeriodCalendarStrategyType::flexStrategy()
                => $this->app->make(FlexStrategyLoader::class),

            PeriodCalendarStrategyType::fadedStrategy()
                => $this->app->make(FadedStrategyLoader::class),

            default => throw new RuntimeException('Unknown strategy loader type given.'),
        };
    }
}
