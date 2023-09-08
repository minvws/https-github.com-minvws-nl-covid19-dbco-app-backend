<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use MinVWS\DBCO\Enum\Models\MutableEnum;
use ErrorException;
use Exception;
use Generator;
use InvalidArgumentException;

use function is_object;

/**
 * @group enum
 */
class EnumTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        MutableEnum::resetEnumSchema();
    }

    protected function setUp(): void
    {
        parent::setUp();
        MutableEnum::resetEnumSchema();
    }

    /**
     * @throws Exception
     */
    private function loadEnumFixture(string $name): void
    {
        $fixture = file_get_contents(sprintf('%s/Fixtures/%s.json', __DIR__, $name));

        if ($fixture === false) {
            throw new Exception('unable to load fixture');
        }

        $schema = json_decode($fixture);

        if (!is_object($schema)) {
            throw new Exception('decoded fixture is not an object');
        }

        MutableEnum::setEnumSchema($schema);
    }

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function testAllValues(): void
    {
        $this->loadEnumFixture('abc');
        $this->assertEquals(7, count(MutableEnum::allValues()));
        $this->assertContainsOnly('string', MutableEnum::allValues());
        $this->assertEquals(['a', 'b', 'c', 'Δ', 'd', 'e', 'f'], MutableEnum::allValues());
    }

    public static function nameAndValueProvider(): Generator
    {
        yield "a"     => ['a',     'a', true];
        yield "b"     => ['b',     'b', true];
        yield "c"     => ['c',     'c', true];
        yield "delta" => ['delta', 'Δ', true];
        yield "d"     => ['d',     'd', true];
        yield "e"     => ['e',     'e', true];
        yield "f"     => ['f',     'f', true];
        yield "g"     => ['g',     'g', false];
    }

    /**
     * @dataProvider nameAndValueProvider
     *
     * @throws Exception
     */
    public function testCallStatic(string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        try {
            $option = MutableEnum::$name();
            $this->assertEquals($exists, $option !== null);
            $this->assertInstanceOf(MutableEnum::class, $option);
            $this->assertEquals($option->value, $value);
        } catch (Exception $e) {
            if (!$exists) {
                $this->assertTrue($e instanceof InvalidArgumentException);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @dataProvider nameAndValueProvider
     *
     * @throws Exception
     */
    public function testForValue(string $name, string $value, bool $exists): void
    {
        $this->loadEnumFixture('abc');

        try {
            $option = MutableEnum::from($value);
            $this->assertEquals($exists, $option !== null);
            $this->assertInstanceOf(MutableEnum::class, $option);
            /** @var MutableEnum $option */
            $this->assertEquals($option->value, $value);
        } catch (Exception $e) {
            if (!$exists) {
                $this->assertTrue($e instanceof InvalidArgumentException);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testCurrentMinMaxVersions(): void
    {
        $this->loadEnumFixture('abc');
        $this->assertEquals(2, MutableEnum::getCurrentVersion()->getVersion());
        $this->assertEquals(1, MutableEnum::getMinVersion()->getVersion());
        $this->assertEquals(3, MutableEnum::getMaxVersion()->getVersion());
    }

    /**
     * @throws Exception
     */
    public function testInvalidVersion(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(InvalidArgumentException::class);
        MutableEnum::getVersion(4);
    }

    /**
     * @throws Exception
     */
    public function testInvalidCallStatic(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        MutableEnum::z();
    }

    /**
     * @throws Exception
     */
    public function testInvalidProperty(): void
    {
        $this->loadEnumFixture('abc');
        $this->expectException(ErrorException::class);
        /** @phpstan-ignore-next-line */
        MutableEnum::a()->invalidProperty;
    }
}
