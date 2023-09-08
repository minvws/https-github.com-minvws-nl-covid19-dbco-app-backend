<?php

declare(strict_types=1);

namespace Tests\Unit\Enum;

use ErrorException;
use Exception;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;

use function count;
use function file_get_contents;
use function json_decode;

#[Group('enum')]
class EnumTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MutableEnum::resetEnumSchema();
    }

    protected function tearDown(): void
    {
        MutableEnum::resetEnumSchema();

        parent::tearDown();
    }

    private function loadEnumFixture(string $name): void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/Fixtures/' . $name . '.json'));
        MutableEnum::setEnumSchema($schema);
    }

    public function testAll(): void
    {
        $this->loadEnumFixture('abc');
        $this->assertEquals(7, count(MutableEnum::all()));
        $this->assertContainsOnlyInstancesOf(MutableEnum::class, MutableEnum::all());
        $this->assertEquals('a', MutableEnum::all()[0]->value);
        $this->assertEquals(1, MutableEnum::all()[0]->indexInAlphabet);
        $this->assertEquals('f', MutableEnum::all()[6]->value);
        $this->assertEquals(6, MutableEnum::all()[6]->indexInAlphabet);
    }

    public function testAllValues(): void
    {
        $this->loadEnumFixture('abc');
        $this->assertEquals(7, count(MutableEnum::allValues()));
        $this->assertContainsOnly('string', MutableEnum::allValues());
        $this->assertEquals(['a', 'b', 'c', 'Δ', 'd', 'e', 'f'], MutableEnum::allValues());
    }

    public static function nameAndValueProvider(): Generator
    {
        yield "a" => ['a', 'a', true];
        yield "b" => ['b', 'b', true];
        yield "c" => ['c', 'c', true];
        yield "delta" => ['delta', 'Δ', true];
        yield "d" => ['d', 'd', true];
        yield "e" => ['e', 'e', true];
        yield "f" => ['f', 'f', true];
        yield "g" => ['g', 'g', false];
    }

    /**
     * @throws Exception
     */
    #[DataProvider('nameAndValueProvider')]
    public function testCallStatic(string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        try {
            $option = MutableEnum::$name();
            $this->assertEquals($exists, $option !== null);
            $this->assertInstanceOf(MutableEnum::class, $option);
            $this->assertEquals($option->value, $value);
        } catch (Throwable $e) {
            if ($exists) {
                throw $e;
            }

            $this->assertTrue($e instanceof InvalidArgumentException);
        }
    }

    #[DataProvider('nameAndValueProvider')]
    public function testForValue(string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        if (!$exists) {
            $this->expectException(InvalidArgumentException::class);
        }

        $option = MutableEnum::from($value);
        $this->assertInstanceOf(MutableEnum::class, $option);
        $this->assertEquals($option->value, $value);
    }

    #[DataProvider('nameAndValueProvider')]
    public function testForValueOrNull(string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        $option = MutableEnum::tryFrom($value);

        if ($exists) {
            $this->assertInstanceOf(MutableEnum::class, $option);
            $this->assertEquals($option->value, $value);
        } else {
            $this->assertNull($option);
        }
    }

    public function testFromArray(): void
    {
        $this->loadEnumFixture('abc');

        $options = MutableEnum::fromArray(['a', 'b', 'c']);

        $expectedResult = [
            MutableEnum::from('a'),
            MutableEnum::from('b'),
            MutableEnum::from('c'),
        ];
        $this->assertEquals($expectedResult, $options);
    }

    public function testForValuesFails(): void
    {
        $this->loadEnumFixture('abc');

        $this->expectException(InvalidArgumentException::class);
        MutableEnum::fromArray(['x', 'y', 'z']);
    }

    public function testTryFromArray(): void
    {
        $this->loadEnumFixture('abc');

        $options = MutableEnum::tryFromArray(['e', 'f', 'g']);

        $expectedResult = [
            MutableEnum::from('e'),
            MutableEnum::from('f'),
        ];
        $this->assertEquals($expectedResult, $options);
    }

    public function testCurrentMinMaxVersions(): void
    {
        $this->loadEnumFixture('abc');
        $this->assertEquals(2, MutableEnum::getCurrentVersion()->getVersion());
        $this->assertEquals(1, MutableEnum::getMinVersion()->getVersion());
        $this->assertEquals(3, MutableEnum::getMaxVersion()->getVersion());
    }

    public function testInvalidVersion(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(InvalidArgumentException::class);
        MutableEnum::getVersion(4);
    }

    public function testInvalidCallStatic(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(InvalidArgumentException::class);
        MutableEnum::z();
    }

    public function testInvalidProperty(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(ErrorException::class);
        MutableEnum::a()->invalidProperty;
    }
}
