<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tests\Faker\WithFaker;

abstract class UnitTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
    use WithFaker;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }
}
