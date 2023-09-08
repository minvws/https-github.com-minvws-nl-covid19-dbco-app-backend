<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Tests\Feature\FeatureTestCase;

class MigrateDataOffHoursTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        $this->artisan('migrate:data:off-hours')
            ->assertSuccessful()
            ->execute();
    }
}
