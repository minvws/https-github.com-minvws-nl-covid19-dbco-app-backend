<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

use function file_get_contents;
use function getcwd;
use function is_array;
use function json_decode;
use function sprintf;

abstract class TestCase extends BaseTestCase
{
    protected Generator $faker;

    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->faker = Factory::create('nl_NL');

        return $app;
    }

    protected static function getRequestPayloadForTestResult(): array
    {
        $filename = 'request.json';

        $json = file_get_contents(sprintf('%s/tests/Stubs/test-result-report/%s', getcwd(), $filename));
        if ($json === false) {
            throw new RuntimeException(sprintf('Failed to load: %s', $filename));
        }

        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new RuntimeException(sprintf('Failed to decode: %s', $filename));
        }

        return $payload;
    }
}
