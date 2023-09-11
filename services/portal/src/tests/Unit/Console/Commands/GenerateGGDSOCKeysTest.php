<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;

class GenerateGGDSOCKeysTest extends TestCase
{
    public function testGenerateGGDSOCKeysRunsWithoutError(): void
    {
        $this->artisan('generate:ggdsockeys')->expectsOutput(
            'Public (URL-safe Base64 encoding, without = padding characters)',
        )->expectsOutput('Secret')->assertExitCode(0);
    }
}
