<?php

declare(strict_types=1);

namespace Tests\Unit\Enum;

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
#[Group('enum-version')]
class EnumVersionTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        MutableEnum::resetEnumSchema();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        MutableEnum::resetEnumSchema();
    }

    private function loadEnumFixture(string $name): void
    {
        $schema = json_decode(file_get_contents(__DIR__ . '/Fixtures/' . $name . '.json'));
        MutableEnum::setEnumSchema($schema);
    }

    public static function allProvider(): Generator
    {
        yield 'V1' => [1, 4, 'a', 'Δ'];
        yield 'V2' => [2, 4, 'a', 'd'];
        yield 'V3' => [3, 6, 'a', 'f'];
    }

    #[DataProvider('allProvider')]
    public function testAll(int $version, int $count, string $first, string $last): void
    {
        $this->loadEnumFixture('abc');

        $version = MutableEnum::getVersion($version);
        $this->assertEquals($count, count($version->all()));
        $this->assertContainsOnlyInstancesOf(MutableEnum::class, $version->all());
        $this->assertEquals($first, $version->all()[0]->value);
        $this->assertEquals($last, $version->all()[$count - 1]->value);
    }

    public static function allValuesProvider(): Generator
    {
        yield "V1" => [
            1,
            ['a', 'b', 'c', 'Δ'],
        ];

        yield "V2" => [
            2,
            ['a', 'b', 'c', 'd'],
        ];

        yield "V3" => [
            3,
            ['a', 'b', 'c', 'd', 'e', 'f'],
        ];
    }

    /**
     * @param array $values
     */
    #[DataProvider('allValuesProvider')]
    public function testAllValues(int $version, array $values): void
    {
        $this->loadEnumFixture('abc');

        $version = MutableEnum::getVersion($version);
        $this->assertEquals(count($values), count($version->allValues()));
        $this->assertContainsOnly('string', $version->allValues());
        $this->assertEquals($values, $version->allValues());
    }

    public static function nameAndValueProvider(): Generator
    {
        yield "V1-a" => [1, 'a', 'a', true];
        yield "V1-b" => [1, 'b', 'b', true];
        yield "V1-c" => [1, 'c', 'c', true];
        yield "V1-delta" => [1, 'delta', 'Δ', true];
        yield "V1-d!" => [1, 'd', 'd', false];
        yield "V1-e!" => [1, 'e', 'e', false];
        yield "V1-f!" => [1, 'f', 'f', false];
        yield "V1-g!" => [1, 'g', 'g', false];

        yield "V2-a" => [2, 'a', 'a', true];
        yield "V2-b" => [2, 'b', 'b', true];
        yield "V2-c" => [2, 'c', 'c', true];
        yield "V2-delta!" => [2, 'delta', 'Δ', false];
        yield "V2-d" => [2, 'd', 'd', true];
        yield "V2-e!" => [2, 'e', 'e', false];
        yield "V2-f!" => [2, 'f', 'f', false];
        yield "V2-g!" => [2, 'g', 'g', false];

        yield "V3-a" => [3, 'a', 'a', true];
        yield "V3-b" => [3, 'b', 'b', true];
        yield "V3-c" => [3, 'c', 'c', true];
        yield "V3-delta!" => [3, 'delta', 'Δ', false];
        yield "V3-d" => [3, 'd', 'd', true];
        yield "V3-e" => [3, 'e', 'e', true];
        yield "V3-f" => [3, 'f', 'f', true];
        yield "V3-g!" => [3, 'g', 'g', false];
    }

    /**
     * @param string|null $value
     *
     * @throws Exception
     */
    #[DataProvider('nameAndValueProvider')]
    public function testCallStatic(int $version, string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        $version = MutableEnum::getVersion($version);

        try {
            $option = $version->$name();
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

    /**
     * @throws Exception
     */
    #[DataProvider('nameAndValueProvider')]
    public function testForValue(int $version, string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        $version = MutableEnum::getVersion($version);

        try {
            $option = $version->from($value);
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
}
