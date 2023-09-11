<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\Calendar\StrategyLoader;

use App\Services\Policy\Calendar\StrategyLoader\FadedStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\FixedStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\FlexStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\NoOpStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoaderFactory;
use Illuminate\Contracts\Foundation\Application;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarStrategyLoader')]
final class StrategyLoaderFactoryTest extends FeatureTestCase
{
    public function testItCanBeInitialized(): void
    {
        $factory = $this->app->make(StrategyLoaderFactory::class);

        $this->assertInstanceOf(StrategyLoaderFactory::class, $factory);
    }

    #[DataProvider('makeData')]
    public function testMakeReturnsCorrectLoader(null|PointCalendarStrategyType|PeriodCalendarStrategyType $strategyLoaderType, string $expectedStrategyLoaderClass): void
    {
        $mockedStrategyLoader = Mockery::mock(StrategyLoader::class);

        /** @var Application&MockInterface $application */
        $application = Mockery::mock(Application::class);
        $application->shouldReceive('make')->with($expectedStrategyLoaderClass)->once()->andReturn($mockedStrategyLoader);

        /** @var StrategyLoaderFactory $factory */
        $factory = $this->app->make(StrategyLoaderFactory::class, ['app' => $application]);

        $strategyLoader = $factory->make($strategyLoaderType);
        $this->assertSame($mockedStrategyLoader, $strategyLoader);
    }

    public static function makeData(): array
    {
        return [
            'null' => [
                'strategyLoaderType' => null,
                'expectedStrategyLoaderClass' => NoOpStrategyLoader::class,
            ],
            PointCalendarStrategyType::fixedStrategy()->value => [
                'strategyLoaderType' => PointCalendarStrategyType::fixedStrategy(),
                'expectedStrategyLoaderClass' => FixedStrategyLoader::class,
            ],
            PointCalendarStrategyType::flexStrategy()->value => [
                'strategyLoaderType' => PointCalendarStrategyType::flexStrategy(),
                'expectedStrategyLoaderClass' => FlexStrategyLoader::class,
            ],
            PeriodCalendarStrategyType::fixedStrategy()->value => [
                'strategyLoaderType' => PeriodCalendarStrategyType::fixedStrategy(),
                'expectedStrategyLoaderClass' => FixedStrategyLoader::class,
            ],
            PeriodCalendarStrategyType::flexStrategy()->value => [
                'strategyLoaderType' => PeriodCalendarStrategyType::flexStrategy(),
                'expectedStrategyLoaderClass' => FlexStrategyLoader::class,
            ],
            PeriodCalendarStrategyType::fadedStrategy()->value => [
                'strategyLoaderType' => PeriodCalendarStrategyType::fadedStrategy(),
                'expectedStrategyLoaderClass' => FadedStrategyLoader::class,
            ],
        ];
    }
}
