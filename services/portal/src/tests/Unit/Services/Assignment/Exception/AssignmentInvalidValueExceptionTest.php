<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentInvalidValueException;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentInvalidValueExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentInvalidValueException();

        $this->assertInstanceOf(AssignmentInvalidValueException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
        $this->assertInstanceOf(RuntimeException::class, $e);
    }

    public function testWrongTypeConstructor(): void
    {
        $e1 = AssignmentInvalidValueException::wrongType('paramName1', 'int', null);
        $e2 = AssignmentInvalidValueException::wrongType('paramName2', 'string', 1);

        $this->assertSame('Invalid type for value "paramName1" given. Expected a "int", but got a "NULL"', $e1->getMessage());
        $this->assertSame('Invalid type for value "paramName2" given. Expected a "string", but got a "integer"', $e2->getMessage());
    }
}
