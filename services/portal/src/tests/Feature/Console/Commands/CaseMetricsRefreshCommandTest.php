<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Tests\Feature\FeatureTestCase;

use function sprintf;

class CaseMetricsRefreshCommandTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        $artisan = $this->artisan('case-metrics:refresh');
        $artisan->assertExitCode(0)
            ->execute();
    }

    public function testCommandWithTimezone(): void
    {
        $timezone = $this->faker->timezone;

        $artisan = $this->artisan(sprintf('case-metrics:refresh --timezone %s', $timezone));
        $artisan->assertExitCode(0)
            ->execute();
    }

    public function testCommandWithInvalidTimezone(): void
    {
        $invalidTimezone = 'FOOBAR';

        $artisan = $this->artisan(sprintf('case-metrics:refresh --timezone %s', $invalidTimezone));
        $artisan->assertExitCode(1)
            ->execute();
    }
}
