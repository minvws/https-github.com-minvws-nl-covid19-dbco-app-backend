<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentUnexpectedValueException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use UnexpectedValueException;

#[Group('assignment')]
class AssignmentUnexpectedValueExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentUnexpectedValueException();

        $this->assertInstanceOf(AssignmentUnexpectedValueException::class, $e);
        $this->assertInstanceOf(UnexpectedValueException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
