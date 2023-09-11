<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Traits;

use App\Console\Traits\WithTypedInput;
use Generator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\InputInterface;
use Tests\Unit\UnitTestCase;

class WithTypedInputTest extends UnitTestCase
{
    private object $withTypedInput;

    private InputInterface&MockInterface $mockInput;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withTypedInput = new class () {
            use WithTypedInput;
        };
        $this->mockInput = Mockery::mock(InputInterface::class);
        $this->withTypedInput->setInput($this->mockInput);
    }

    #[DataProvider('provideGetStringTestScenarios')]
    public function testGetStringArgumentSupportsAllPossibleParameterTypes(mixed $argumentMethodOutput, string $default, string $expectation): void
    {
        $this->mockInput->shouldReceive(['getArgument' => $argumentMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getStringArgument('foo', $default));
    }

    #[DataProvider('provideGetStringTestScenarios')]
    public function testGetStringOptionSupportsAllPossibleParameterTypes(mixed $optionMethodOutput, string $default, string $expectation): void
    {
        $this->mockInput->shouldReceive(['getOption' => $optionMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getStringOption('foo', $default));
    }

    public static function provideGetStringTestScenarios(): Generator
    {
        yield 'array parameter' => [['someArrayValue', 'someArrayValue'], 'imDefault', 'imDefault'];
        yield 'bool parameter' => [true, 'imDefault', 'imDefault'];
        yield 'null parameter' => [null, 'imDefault', 'imDefault'];
        yield 'non-numeric string parameter' => ['someNonNumericStringValue', 'imDefault', 'someNonNumericStringValue'];
        yield 'numeric string parameter' => ['123', 'imDefault', '123'];
    }

    #[DataProvider('provideGetIntegerTestScenarios')]
    public function testGetIntegerArgumentSupportsAllPossibleParameterTypes(mixed $argumentMethodOutput, int $default, int $expectation): void
    {
        $this->mockInput->shouldReceive(['getArgument' => $argumentMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getIntegerArgument('foo', $default));
    }

    #[DataProvider('provideGetIntegerTestScenarios')]
    public function testGetIntegerOptionSupportsAllPossibleParameterTypes(mixed $optionMethodOutput, int $default, int $expectation): void
    {
        $this->mockInput->shouldReceive(['getOption' => $optionMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getIntegerOption('foo', $default));
    }

    public static function provideGetIntegerTestScenarios(): Generator
    {
        yield 'array parameter' => [['someArrayValue', 'someArrayValue'], 999, 999];
        yield 'bool parameter' => [true, 999, 999];
        yield 'null parameter' => [null, 999, 999];
        yield 'non-numeric string parameter' => ['someNonNumericStringValue', 999, 999];
        yield 'numeric string parameter' => ['123', 999, 123];
        yield 'float string parameter' => ['12.3', 999, 12];
    }

    #[DataProvider('provideGetBooleanTestScenarios')]
    public function testGetBooleanArgumentSupportsAllPossibleParameterTypes(mixed $argumentMethodOutput, bool $expectation): void
    {
        $this->mockInput->shouldReceive(['getArgument' => $argumentMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getBooleanArgument('foo'));
    }

    #[DataProvider('provideGetBooleanTestScenarios')]
    public function testGetBooleanOptionSupportsAllPossibleParameterTypes(mixed $optionMethodOutput, bool $expectation): void
    {
        $this->mockInput->shouldReceive(['getOption' => $optionMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getBooleanOption('foo'));
    }

    public static function provideGetBooleanTestScenarios(): Generator
    {
        yield 'array parameter' => [['someArrayValue', 'someArrayValue'], false];
        yield 'bool true parameter' => [true, true];
        yield 'bool false parameter' => [false, false];
        yield 'non-boolean string parameter' => ['someNonBooleanString', false];
        yield 'null parameter' => [null, false];
        yield 'truthy string parameter "true"' => ['true', true];
        yield 'truthy string parameter "1"' => ['1', true];
        yield 'truthy string parameter "on"' => ['on', true];
        yield 'falsy string parameter "false"' => ['false', false];
        yield 'falsy string parameter "0"' => ['0', false];
        yield 'falsy string parameter "off"' => ['off', false];
    }

    #[DataProvider('provideGetArrayTestScenarios')]
    public function testGetArrayOptionSupportsAllPossibleParameterTypes(mixed $optionMethodOutput, array $default, array $expectation): void
    {
        $this->mockInput->shouldReceive(['getOption' => $optionMethodOutput]);

        $this->assertEquals($expectation, $this->withTypedInput->getArrayOption('foo', $default));
    }

    public static function provideGetArrayTestScenarios(): Generator
    {
        yield 'array parameter' => [['someArrayValue', 'someArrayValue'], [], ['someArrayValue', 'someArrayValue']];
        yield 'bool parameter' => [true, [], []];
        yield 'null parameter' => [null, [], []];
        yield 'string parameter' => ['someNonNumericStringValue', [], []];
    }
}
