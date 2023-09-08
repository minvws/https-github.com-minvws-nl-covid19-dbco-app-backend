<?php

declare(strict_types=1);

namespace MinVWS\Timer\Tests;

use MinVWS\Timer\Duration;
use PHPUnit\Framework\TestCase;

final class DurationTest extends TestCase
{
    public function testCanBeCreatedFromNanoseconds(): void
    {
        $duration = Duration::fromNanoseconds(1);
        $this->assertSame(1.0E-9, $duration->inSeconds());
    }

    public function testCanBeCreatedFromSeconds(): void
    {
        $duration = Duration::fromSeconds(5.0);
        $this->assertSame(5.0, $duration->inSeconds());
    }
}
