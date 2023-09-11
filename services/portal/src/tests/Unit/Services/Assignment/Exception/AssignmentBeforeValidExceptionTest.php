<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentBeforeValidException;
use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentBeforeValidExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentBeforeValidException();

        $this->assertInstanceOf(AssignmentBeforeValidException::class, $e);
        $this->assertInstanceOf(AssignmentUnexpectedValueException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
