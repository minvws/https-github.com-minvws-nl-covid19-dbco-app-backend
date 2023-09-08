<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\Environment;
use Illuminate\Support\Facades\App;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[DataProvider('booleanDataProvider')]
    public function testIsDevelopment(bool $value): void
    {
        App::expects('environment')
            ->with(['dev', 'development', 'local'])
            ->andReturn($value);

        $this->assertEquals($value, Environment::isDevelopment());
    }

    #[DataProvider('booleanDataProvider')]
    public function testIsProduction(bool $value): void
    {
        App::expects('environment')
            ->with(['production'])
            ->andReturn($value);

        $this->assertEquals($value, Environment::isProduction());
    }

    #[DataProvider('booleanDataProvider')]
    public function testIsTesting(bool $value): void
    {
        App::expects('environment')
            ->with(['test', 'testing'])
            ->andReturn($value);

        $this->assertEquals($value, Environment::isTesting());
    }

    #[DataProvider('isDevelopmentOrTestingData')]
    public function testIsDevelopmentOrTesting(bool $isDevelopment, bool $isTesting, bool $expected): void
    {
        App::expects('environment')
            ->with(['dev', 'development', 'local'])
            ->zeroOrMoreTimes()
            ->andReturn($isDevelopment);

        App::expects('environment')
            ->with(['test', 'testing'])
            ->zeroOrMoreTimes()
            ->andReturn($isTesting);

        $this->assertEquals($expected, Environment::isDevelopmentOrTesting());
    }

    public function testEnvironmentReturnsString(): void
    {
        App::expects('environment')->andReturn($this->faker->word());

        $this->expectException(RuntimeException::class);
        Environment::isTesting();
    }

    public static function booleanDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public static function isDevelopmentOrTestingData(): array
    {
        return [
            'its neither testing nor development' => [
                'isDevelopment' => false,
                'isTesting' => false,
                'expected' => false,
            ],
            'its testing' => [
                'isDevelopment' => false,
                'isTesting' => true,
                'expected' => true,
            ],
            'its development' => [
                'isDevelopment' => true,
                'isTesting' => false,
                'expected' => true,
            ],
            // this should normally never be the case
            'its both testing and development' => [
                'isDevelopment' => true,
                'isTesting' => true,
                'expected' => true,
            ],
        ];
    }
}
