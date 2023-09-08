<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Metric\CircuitBreaker;

use App\Models\Metric\CircuitBreaker\Availability;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function sprintf;

#[Group('metric')]
class AvailabilityTest extends UnitTestCase
{
    #[DataProvider('availabilityDataProvider')]
    public function testMetric(string $availability, float $expectedValue): void
    {
        $service = $this->faker->word();

        $metric = Availability::$availability($service);

        $this->assertEquals(sprintf('%s_circuit_breaker_gauge', $service), $metric->getName());
        $this->assertEquals(sprintf('Gauge for the circuit breaker used for: "%s"', $service), $metric->getHelp());
        $this->assertEquals([], $metric->getLabels());
        $this->assertIsFloat($metric->getValue());
        $this->assertEquals($expectedValue, $metric->getValue());
    }

    public static function availabilityDataProvider(): array
    {
        return [
            'available' => ['available', 0.0],
            'notAvailable' => ['notAvailable', 1.0],
        ];
    }
}
