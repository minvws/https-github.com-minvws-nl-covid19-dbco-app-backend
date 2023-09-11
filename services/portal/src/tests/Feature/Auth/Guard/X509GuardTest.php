<?php

declare(strict_types=1);

namespace Tests\Feature\Auth\Guard;

use App\Auth\Guard\X509Guard;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

class X509GuardTest extends FeatureTestCase
{
    public function testExportGuardUsesX509Driver(): void
    {
        $guard = Auth::guard('export');
        $this->assertTrue($guard instanceof X509Guard);
    }

    public function testValidateUnsupported(): void
    {
        $guard = Auth::guard('export');
        $this->expectException(RuntimeException::class);
        $guard->validate([]);
    }
}
