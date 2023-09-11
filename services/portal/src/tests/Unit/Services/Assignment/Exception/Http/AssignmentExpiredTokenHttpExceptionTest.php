<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Exception\Http;

use App\Services\Assignment\Exception\AssignmentException;
use App\Services\Assignment\Exception\Http\AssignmentExpiredTokenHttpException;
use App\Services\Assignment\Exception\Http\AssignmentHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('assignment')]
class AssignmentExpiredTokenHttpExceptionTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $e = new AssignmentExpiredTokenHttpException();

        $this->assertInstanceOf(AssignmentExpiredTokenHttpException::class, $e);
        $this->assertInstanceOf(HttpResponseException::class, $e);
        $this->assertInstanceOf(AssignmentHttpException::class, $e);
        $this->assertInstanceOf(AssignmentException::class, $e);
    }
}
