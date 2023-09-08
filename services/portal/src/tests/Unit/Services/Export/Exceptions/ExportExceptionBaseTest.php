<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Exceptions;

use App\Services\Export\Exceptions\ExportException;
use App\Services\Export\Exceptions\ExportRuntimeException;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;
use Throwable;

class ExportExceptionBaseTest extends UnitTestCase
{
    private function testResultException(Throwable $originalException, Throwable $resultException, string $expectedExceptionClass, string $expectedMessage, bool $expectExceptionToBeWrapped): void
    {
        if ($expectExceptionToBeWrapped) {
            $this->assertInstanceOf($expectedExceptionClass, $resultException);
        } else {
            $this->assertTrue($resultException === $originalException);
        }

        $this->assertEquals($expectedMessage, $resultException->getMessage());

        if ($expectExceptionToBeWrapped) {
            $this->assertTrue($resultException->getPrevious() === $originalException);
        } else {
            $this->assertTrue($resultException === $originalException);
        }
    }

    public static function exceptionProvider(): Generator
    {
        yield [new Exception('Test'), null, 'Test', true];
        yield [new Exception('Test'), 'Override', 'Override', true];
    }

    public static function exportExceptionProvider(): Generator
    {
        yield [new ExportException('Test'), null, 'Test', false];
        yield [new ExportException('Test'), 'Override', 'Override', true];
    }

    public static function exportRuntimeExceptionProvider(): Generator
    {
        yield [new ExportRuntimeException('Test'), null, 'Test', false];
        yield [new ExportRuntimeException('Test'), 'Override', 'Override', true];
    }

    #[DataProvider('exceptionProvider')]
    #[DataProvider('exportExceptionProvider')]
    public function testExportExceptionFrom(Throwable $exception, ?string $message, string $expectedMessage, bool $expectExceptionToBeWrapped): void
    {
        $resultException = ExportException::from($exception, $message);
        $this->testResultException($exception, $resultException, ExportException::class, $expectedMessage, $expectExceptionToBeWrapped);
    }

    #[DataProvider('exceptionProvider')]
    #[DataProvider('exportRuntimeExceptionProvider')]
    public function testExportRuntimeExceptionFrom(Throwable $exception, ?string $message, string $expectedMessage, bool $expectExceptionToBeWrapped): void
    {
        $resultException = ExportRuntimeException::from($exception, $message);
        $this->testResultException(
            $exception,
            $resultException,
            ExportRuntimeException::class,
            $expectedMessage,
            $expectExceptionToBeWrapped,
        );
    }
}
