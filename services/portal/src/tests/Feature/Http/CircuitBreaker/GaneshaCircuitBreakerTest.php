<?php

declare(strict_types=1);

namespace Tests\Feature\Http\CircuitBreaker;

use Ackintosh\Ganesha;
use App\Http\CircuitBreaker\GaneshaCircuitBreaker;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\FeatureTestCase;

class GaneshaCircuitBreakerTest extends FeatureTestCase
{
    public function testIsAvailable(): void
    {
        $service = $this->faker->word();
        $returnValue = $this->faker->boolean();

        /** @var Ganesha|MockObject $ganesha */
        $ganesha = $this->createMock(Ganesha::class);
        $ganesha->expects($this->once())
            ->method('isAvailable')
            ->with($service)
            ->willReturn($returnValue);

        $ganeshaCircuitBreaker = new GaneshaCircuitBreaker($ganesha);
        $this->assertEquals($returnValue, $ganeshaCircuitBreaker->isAvailable($service));
    }

    public function testRegisterFailure(): void
    {
        $service = $this->faker->word();

        /** @var Ganesha|MockObject $ganesha */
        $ganesha = $this->createMock(Ganesha::class);
        $ganesha->expects($this->once())
            ->method('failure')
            ->with($service);

        $ganeshaCircuitBreaker = new GaneshaCircuitBreaker($ganesha);
        $ganeshaCircuitBreaker->registerFailure($service);
    }

    public function testRegisterSuccess(): void
    {
        $service = $this->faker->word();

        /** @var Ganesha|MockObject $ganesha */
        $ganesha = $this->createMock(Ganesha::class);
        $ganesha->expects($this->once())
            ->method('success')
            ->with($service);

        $ganeshaCircuitBreaker = new GaneshaCircuitBreaker($ganesha);
        $ganeshaCircuitBreaker->registerSuccess($service);
    }
}
