<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception\Http;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\Http\AssignmentHttpException;
use App\Services\Assignment\Exception\Http\AssignmentInvalidTokenHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentInvalidTokenHttpExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentInvalidTokenHttpException();

        $this->assertInstanceOf(AssignmentInvalidTokenHttpException::class, $e);
        $this->assertInstanceOf(HttpResponseException::class, $e);
        $this->assertInstanceOf(AssignmentHttpException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
