<?php

declare(strict_types=1);

namespace Tests\Unit\Services\BcoNumber;

use App\Services\BcoNumber\BcoNumberException;
use App\Services\BcoNumber\BcoNumberGenerator;
use Tests\Unit\UnitTestCase;

class BcoNumberGeneratorTest extends UnitTestCase
{
    public function testBuildCode(): void
    {
        $bcoNumberGenerator = new BcoNumberGenerator('1', 'a');
        $code = $bcoNumberGenerator->buildCode();

        $this->assertEquals('aa1-111-111', $code);
    }

    public function testBuildCodeInvalidNumberChars(): void
    {
        $bcoNumberGenerator = new BcoNumberGenerator('', 'a');

        $this->expectException(BcoNumberException::class);
        $bcoNumberGenerator->buildCode();
    }

    public function testBuildCodeInvalidAlphaChars(): void
    {
        $bcoNumberGenerator = new BcoNumberGenerator('1', '');

        $this->expectException(BcoNumberException::class);
        $bcoNumberGenerator->buildCode();
    }
}
