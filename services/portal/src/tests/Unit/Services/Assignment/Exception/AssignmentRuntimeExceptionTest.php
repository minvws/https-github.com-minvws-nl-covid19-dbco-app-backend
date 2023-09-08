<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentRuntimeException;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentRuntimeExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentRuntimeException();

        $this->assertInstanceOf(AssignmentRuntimeException::class, $e);
        $this->assertInstanceOf(RuntimeException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
