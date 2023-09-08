<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Metric\Test\Test;
use App\Services\MetricService;
use Illuminate\Console\Command;

use function is_string;

class MetricTest extends Command
{
    /** @var string */
    protected $signature = 'metric:test
        {message=test : The message to pass with the metric (as a label)}
    ';

    /** @var string */
    protected $description = 'Trigger a test-metric';

    public function handle(MetricService $metricService): int
    {
        $this->info('Test metric triggered');

        $message = $this->argument('message');
        if (!is_string($message)) {
            $this->error('message is not a string');

            return self::FAILURE;
        }

        $metricService->measure(Test::withMessage($message));

        return self::SUCCESS;
    }
}
