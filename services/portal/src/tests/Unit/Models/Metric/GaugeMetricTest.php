<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric;

use App\Models\Metric\GaugeMetric;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('metric')]
class GaugeMetricTest extends UnitTestCase
{
    public function testGaugeMetric(): void
    {
        $gaugeMetric = new class extends GaugeMetric
        {
            protected string $name = 'name';
            protected string $help = 'help';
            protected float $value = 1.0;
        };

        $this->assertEquals('help', $gaugeMetric->getHelp());
        $this->assertEquals('name', $gaugeMetric->getName());
        $this->assertEquals([], $gaugeMetric->getLabels());
        $this->assertEquals(1.0, $gaugeMetric->getValue());
    }
}
