<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentSignatureInvalidException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssigmentSignatureInvalidExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentSignatureInvalidException();

        $this->assertInstanceOf(AssignmentSignatureInvalidException::class, $e);
        $this->assertInstanceOf(AssignmentUnexpectedValueException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
