<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\AssignmentInternalValidationException;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Unit\UnitTestCase;
use Throwable;

#[Group('assignment')]
class AssignmentInternalValidationExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        /** @var ValidationException&MockInterface $validationException */
        $validationException = Mockery::mock(ValidationException::class);

        $e = new AssignmentInternalValidationException(
            message: '',
            validationException: $validationException,
            status: 500,
            headers: [],
        );

        $this->assertInstanceOf(AssignmentInternalValidationException::class, $e);
        $this->assertInstanceOf(Throwable::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
        $this->assertInstanceOf(HttpExceptionInterface::class, $e);
    }

    public function testHttpExceptionInterfaceMethods(): void
    {
        /** @var ValidationException&MockInterface $validationException */
        $validationException = Mockery::mock(ValidationException::class);

        $e = new AssignmentInternalValidationException(
            message: '',
            validationException: $validationException,
            status: $status = 400,
            headers: $headers = ['Some-Header' => 'header contents'],
        );

        $this->assertSame($status, $e->getStatusCode());
        $this->assertSame($headers, $e->getHeaders());
    }
}
