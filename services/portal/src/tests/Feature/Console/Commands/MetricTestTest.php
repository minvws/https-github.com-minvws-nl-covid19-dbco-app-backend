<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Metric\Test\Test;
use App\Repositories\Metric\MetricRepository;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;

final class MetricTestTest extends FeatureTestCase
{
    public function testExecution(): void
    {
        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->once()
                ->with(Mockery::on(static function (Test $test): bool {
                    if ($test->getName() !== 'test') {
                        return false;
                    }

                    return $test->getLabels() === ['message' => 'test'];
                }));
        });

        $this->artisan('metric:test')
            ->assertOk();
    }

    public function testExecutionWithMessage(): void
    {
        $message = $this->faker->sentence;

        $this->mock(MetricRepository::class, static function (MockInterface $mock) use ($message): void {
            $mock->expects('measureCounter')
                ->once()
                ->with(Mockery::on(static function (Test $test) use ($message): bool {
                    if ($test->getName() !== 'test') {
                        return false;
                    }

                    return $test->getLabels() === ['message' => $message];
                }));
        });

        $this->artisan('metric:test', ['message' => $message])
            ->assertOk();
    }

    public function testExecutionWithNonStringMessage(): void
    {
        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->never()
                ->with(Mockery::on(static function (Test $test): bool {
                    return $test->getName() === 'test';
                }));
        });

        $message = [$this->faker->word() => $this->faker->sentence];
        $this->artisan('metric:test', ['message' => $message])
            ->assertFailed();
    }
}
