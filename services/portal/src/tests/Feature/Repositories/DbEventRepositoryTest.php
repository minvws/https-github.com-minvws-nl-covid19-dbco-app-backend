<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Event;
use App\Repositories\EventRepository;
use Database\Seeders\EventSeeder;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('export-event')]
class DbEventRepositoryTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->case = $this->createCase();
        $this->repo = app(EventRepository::class);
        $this->seed(EventSeeder::class);
    }

    public function testItForwardsCallsToEloquent(): void
    {
        $event = $this->createEvent();
        $event = $this->repo->getByUuid($event->uuid);
        self::assertInstanceOf(Event::class, $event);
    }
}
