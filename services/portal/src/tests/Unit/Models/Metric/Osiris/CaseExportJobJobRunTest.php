<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\Osiris;

use App\Models\Metric\Osiris\CaseExportJobRun;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('metric')]
class CaseExportJobJobRunTest extends UnitTestCase
{
    #[DataProvider('statusDataProvider')]
    public function testMetric(string $status): void
    {
        $metric = CaseExportJobRun::$status();

        $this->assertEquals(['status' => $status], $metric->getLabels());
    }

    public static function statusDataProvider(): array
    {
        return [
            'success' => ['success'],
            'failed' => ['failed'],
        ];
    }
}
