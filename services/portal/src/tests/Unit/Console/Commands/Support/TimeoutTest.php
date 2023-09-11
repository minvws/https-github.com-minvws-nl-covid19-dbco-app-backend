<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Support;

use App\Console\Commands\Support\Timeout;
use Carbon\CarbonImmutable;
use Tests\Unit\UnitTestCase;

final class TimeoutTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(Timeout::class, new Timeout());
    }

    public function testItCanReturnNow(): void
    {
        CarbonImmutable::setTestNow('now');

        $this->assertEquals(CarbonImmutable::now(), (new Timeout())->getNow());
    }

    public function testICanCheckIfATimeoutIsNotSet(): void
    {
        $this->assertFalse((new Timeout())->isSet(), 'Timeout should not be set');
    }

    public function testICanCheckIfATimeoutIsSet(): void
    {
        $timeout = (new Timeout())->setTimeoutInMinutes($this->faker->numberBetween(1, 10));

        $this->assertTrue($timeout->isSet(), 'Timeout should be set');
    }

    public function testTimeOutWithNothingSet(): void
    {
        $this->assertFalse((new Timeout())->timedOut(), 'Should not be timed out');
    }

    public function testTimeOutWithATimeoutSetInMinutes(): void
    {
        $timeout = (new Timeout())->setTimeoutInMinutes($this->faker->numberBetween(5, 60));

        $this->assertFalse($timeout->timedOut(), 'Should not be timed out');
    }

    public function testTimeOutWithATimeOutSetTo10MinutesAndCurrentTimeSetToNowPlus15Minutes(): void
    {
        $timeout = (new Timeout())->setTimeoutInMinutes(10);

        CarbonImmutable::setTestNow('now + 15 minutes');

        $this->assertTrue($timeout->timedOut(), 'Should be timed out');
    }
}
