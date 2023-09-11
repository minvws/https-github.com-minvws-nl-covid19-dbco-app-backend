<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Tests\Feature\FeatureTestCase;

class ArchiveStaleCompletedCasesTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        $artisan = $this->artisan('cases:archive-stale-completed-cases');
        $artisan->expectsOutput('Archiving stale completed cases...')
            ->expectsOutput('Archived 0 stale completed cases')
            ->assertExitCode(0)
            ->execute();
    }
}
