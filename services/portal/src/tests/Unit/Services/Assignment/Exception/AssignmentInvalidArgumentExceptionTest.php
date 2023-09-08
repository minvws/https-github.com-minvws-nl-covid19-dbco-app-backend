<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentInvalidArgumentException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentInvalidArgumentExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentInvalidArgumentException();

        $this->assertInstanceOf(AssignmentInvalidArgumentException::class, $e);
        $this->assertInstanceOf(InvalidArgumentException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
