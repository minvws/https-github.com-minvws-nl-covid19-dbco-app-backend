<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentDomainException;
use App\Services\Assignment\Exception\AssignmentException;
use DomainException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentDomainExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentDomainException();

        $this->assertInstanceOf(AssignmentDomainException::class, $e);
        $this->assertInstanceOf(DomainException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
