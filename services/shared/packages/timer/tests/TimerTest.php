<?php

declare(strict_types=1);

namespace MinVWS\Timer\Tests;

use MinVWS\Timer\Timer;
use Error;
use PHPUnit\Framework\TestCase;

final class TimerTest extends TestCase
{
    public function testMustBeStartedBeforeItIsStopped(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage(
            'Typed property MinVWS\\Timer\\Timer::$start must not be accessed before initialization',
        );

        $timer = Timer::start();
        $timer->stop();

        $this->expectException(Error::class);
        $timer->stop();
    }

    public function testCanBeStartedAndStopped(): void
    {
        $timer = Timer::start();
        $duration = $timer->stop();
        $timer = $timer->start();
        $this->assertNotSame($duration, $timer->stop());
    }

    public function testCanBeWrappedAroundCallable(): void
    {
        Timer::wrap(
            callable: function (): void {
                $this->assertTrue(true);
            }
        );
    }
}
