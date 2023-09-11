<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\ArrayReader;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class ArrayReaderTest extends UnitTestCase
{
    #[DataProvider('provideGetArrayByKeyInput')]
    public function testGetArrayByKey(mixed $data, string|int $key, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertIsArray(ArrayReader::getArrayByKey($data, $key));
    }

    public static function provideGetArrayByKeyInput(): Generator
    {
        // Args: mixed $data, string|int $key, bool $valid
        yield 'data argument not of type array' => ['notAnArray', 'somePropertyName', false];
        yield 'property argument non-existent' => [['arrayProperty' => ['foo' => 'bar']], 'nonExistingProperty', false];
        yield 'data property is of type string instead of type array' => [['arrayProperty' => 'notAnArray'], 'arrayProperty', false];
        yield 'data property is of type bool instead of type array' => [['arrayProperty' => true], 'arrayProperty', false];
        yield 'data property is of type int instead of type array' => [['arrayProperty' => 999], 'arrayProperty', false];
        yield 'valid data and property arguments' => [['arrayProperty' => ['foo' => 'bar']], 'arrayProperty', true];
    }

    #[DataProvider('provideGetIntegerByKeyInput')]
    public function testGetIntegerByKey(mixed $data, string|int $key, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertIsInt(ArrayReader::getIntegerByKey($data, $key));
    }

    public static function provideGetIntegerByKeyInput(): Generator
    {
        // Args: mixed $data, string|int $key, bool $valid
        yield 'data argument not of type array' => ['notAnArray', 'somePropertyName', false];
        yield 'property argument non-existent' => [['arrayProperty' => 999], 'nonExistingKey', false];
        yield 'data property is of type string instead of type integer' => [['arrayProperty' => 'notAnInteger'], 'arrayProperty', false];
        yield 'data property is of type bool instead of type integer' => [['arrayProperty' => true], 'arrayProperty', false];
        yield 'data property is of type array instead of type integer' => [['arrayProperty' => [999]], 'arrayProperty', false];
        yield 'data property is of type float instead of type integer' => [['arrayProperty' => 99.99], 'arrayProperty', false];
        yield 'valid data and property arguments' => [['arrayProperty' => 999], 'arrayProperty', true];
    }

    #[DataProvider('provideGetStringByKeyInput')]
    public function testGetStringByKey(mixed $data, string|int $key, bool $valid): void
    {
        if (!$valid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $this->assertIsString(ArrayReader::getStringByKey($data, $key));
    }

    public static function provideGetStringByKeyInput(): Generator
    {
        // Args: mixed $data, string|int $key, bool $valid
        yield 'data argument not of type array' => ['notAnArray', 'somePropertyName', false];
        yield 'property argument non-existent' => [['arrayProperty' => 'foobar'], 'nonExistingProperty', false];
        yield 'data property is of type int instead of type string' => [['arrayProperty' => 999], 'arrayProperty', false];
        yield 'data property is of type bool instead of type string' => [['arrayProperty' => true], 'arrayProperty', false];
        yield 'data property is of type array instead of type string' => [['arrayProperty' => ['foobar']], 'arrayProperty', false];
        yield 'valid data and property arguments' => [['arrayProperty' => 'foobar'], 'arrayProperty', true];
    }

    public function testGetIntegerOrNullByKey(): void
    {
        $this->assertNull(ArrayReader::getIntegerOrNullByKey(['arrayProperty' => null], 'arrayProperty'));
    }

    public function testGetStringOrNullByKey(): void
    {
        $this->assertNull(ArrayReader::getStringOrNullByKey(['arrayProperty' => null], 'arrayProperty'));
    }
}
