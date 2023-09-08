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
use App\Services\Policy\Calendar\StrategyLoader\DateOperationStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\FixedStrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoader;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarStrategyLoader')]
final class FixedStrategyLoaderTest extends FeatureTestCase
{
    use AbstractDateOperationStrategyLoaderHelper;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testItCanBeInitialized(): void
    {
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $this->assertInstanceOf(FixedStrategyLoader::class, $strategyLoader);
        $this->assertInstanceOf(AbstractDateOperationStrategyLoader::class, $strategyLoader);
        $this->assertInstanceOf(StrategyLoader::class, $strategyLoader);
        $this->assertInstanceOf(DateOperationStrategyLoader::class, $strategyLoader);
    }

    public function testItCanInitializePointCalendarItemConfig(): void
    {
        /** @var FixedStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItemConfig = CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->index()->point())
            ->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()
            ->recycle($calendarItemConfig)
            ->create();

        $this->assertDatabaseCount(DateOperation::class, 0);

        $dateOperations = $strategyLoader->getInitializeData($calendarItemConfigStrategy);

        $this->assertCount(1, $dateOperations);
        $this->assertDatabaseCount(DateOperation::class, 0);
        $this->assertEquals(
            new CreateDateOperationDto(
                identifier: DateOperationIdentifier::default(),
                mutation: DateOperationMutation::add(),
                amount: 0,
                unitOfTime: UnitOfTime::day(),
                originDate: IndexOriginDate::defaultItem(),
            ),
            $dateOperations->first(),
        );
    }

    public function testItCanInitializePeriodCalendarItemConfig(): void
    {
        /** @var FixedStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $policyVersion = PolicyVersion::factory()->create();
        $calendarItemConfig = CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->contact()->period())
            ->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()
            ->recycle($calendarItemConfig)
            ->create();

        $this->assertDatabaseCount(DateOperation::class, 0);

        $dateOperations = $strategyLoader->getInitializeData($calendarItemConfigStrategy);

        $this->assertCount(1, $dateOperations);
        $this->assertDatabaseCount(DateOperation::class, 0);
        $this->assertEquals(
            new CreateDateOperationDto(
                identifier: DateOperationIdentifier::default(),
                mutation: DateOperationMutation::add(),
                amount: 0,
                unitOfTime: UnitOfTime::day(),
                originDate: ContactOriginDate::defaultItem(),
            ),
            $dateOperations->first(),
        );
    }

    public function testItCanGetDateOperations(): void
    {
        /** @var FixedStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $this->itCanGetDateOperations($strategyLoader);
    }

    public function testItCanDeleteDateOperations(): void
    {
        /** @var FixedStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $this->itCanDeleteDateOperations($strategyLoader);
    }

    public function testItCanSetDateOperations(): void
    {
        /** @var FixedStrategyLoader $strategyLoader */
        $strategyLoader = $this->app->make(FixedStrategyLoader::class);

        $this->itCanSetDateOperations($strategyLoader);
    }
}
