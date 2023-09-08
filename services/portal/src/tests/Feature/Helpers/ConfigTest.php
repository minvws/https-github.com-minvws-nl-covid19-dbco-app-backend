<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\Config;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

class ConfigTest extends FeatureTestCase
{
    public function testArray(): void
    {
        $configKey = $this->faker->word();
        $configValue = [$this->faker->word() => $this->faker->boolean];

        config()->set($configKey, $configValue);

        $this->assertEquals($configValue, Config::array($configKey));
    }

    public function testArrayIsNotAnArray(): void
    {
        $configKey = $this->faker->word();
        $configValue = $this->faker->boolean;

        config()->set($configKey, $configValue);

        $this->expectException(InvalidArgumentException::class);
        $this->assertEquals($configValue, Config::array($configKey));
    }

    public function testBoolean(): void
    {
        $configKey = $this->faker->word();
        $configValue = $this->faker->boolean;

        config()->set($configKey, $configValue);

        $this->assertEquals($configValue, Config::boolean($configKey));
    }

    public function testBooleanIsNotABoolean(): void
    {
        $configKey = $this->faker->word();

        config()->set($configKey, []);

        $this->expectException(InvalidArgumentException::class);
        Config::boolean($configKey);
    }

    #[DataProvider('integerProvider')]
    public function testInteger(mixed $configValue, int $expectedValue): void
    {
        $configKey = $this->faker->word();
        config()->set($configKey, $configValue);

        $this->assertSame($expectedValue, Config::integer($configKey));
    }

    public static function integerProvider(): array
    {
        return [
            'positive integer' => [15, 15],
            'zero integer' => [0, 0],
            'negative integer' => [-5, -5],
            'positive string' => ['123', 123],
            'zero string' => ['0', 0],
            'negative string' => ['-5', -5],
        ];
    }

    public function testIntegerIsNotAnInteger(): void
    {
        $configKey = $this->faker->word();

        config()->set($configKey, []);

        $this->expectException(InvalidArgumentException::class);
        Config::integer($configKey);
    }

    public function testString(): void
    {
        $configKey = $this->faker->word();
        $configValue = $this->faker->word();

        config()->set($configKey, $configValue);

        $this->assertEquals($configValue, Config::string($configKey));
    }

    public function testStringIsNotAString(): void
    {
        $configKey = $this->faker->word();

        config()->set($configKey, []);

        $this->expectException(InvalidArgumentException::class);
        Config::string($configKey);
    }

    public function testStringOrNull(): void
    {
        $configKey = $this->faker->word();
        $configValue = $this->faker->optional()->word();

        config()->set($configKey, $configValue);

        $this->assertEquals($configValue, Config::stringOrNull($configKey));
    }

    public function testStringOrNullIsNotAStringOrNull(): void
    {
        $configKey = $this->faker->word();

        config()->set($configKey, []);

        $this->expectException(InvalidArgumentException::class);
        Config::stringOrNull($configKey);
    }
}
