<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\Osiris;

use App\Models\Metric\Osiris\CaseExportJobAttempts;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function range;

#[Group('metric')]
class CaseExportJobAttemptsTest extends UnitTestCase
{
    #[DataProvider('statusDataProvider')]
    public function testMetric(string $status): void
    {
        $attempts = $this->faker->randomFloat();
        $maxTries = $this->faker->numberBetween(1, 9);

        $metric = CaseExportJobAttempts::$status($attempts, $maxTries);

        $this->assertEquals(['status' => $status], $metric->getLabels());
        $this->assertEquals($attempts, $metric->getValue());
        $this->assertEquals(range(1, $maxTries), $metric->getBuckets());
    }

    public static function statusDataProvider(): array
    {
        return [
            'success' => ['success'],
            'failed' => ['failed'],
        ];
    }

    public function testZeroMaxTriesFails(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CaseExportJobAttempts::success(1, 0);
    }
}
