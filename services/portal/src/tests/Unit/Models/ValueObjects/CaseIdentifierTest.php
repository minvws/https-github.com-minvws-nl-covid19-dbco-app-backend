<?php

declare(strict_types=1);

namespace Tests\Unit\Models\ValueObjects;

use App\Models\ValueObjects\CaseIdentifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Unit\UnitTestCase;

class CaseIdentifierTest extends UnitTestCase
{
    #[DataProvider('monsterNumberProvider')]
    #[TestDox('$monsterNumber is recognised as a monsternumber')]
    public function testMonsterNumber(string $monsterNumber): void
    {
        $caseIdentifier = new CaseIdentifier($monsterNumber);

        $this->assertFalse($caseIdentifier->isBcoPortalNumber());
        $this->assertFalse($caseIdentifier->isHpzoneNumber());
        $this->assertTrue($caseIdentifier->isMonsterNumber());
    }

    public static function monsterNumberProvider(): array
    {
        return [
            ['123A123123'],
            ['123A123123123'],
        ];
    }

    #[TestDox('AA1-123-123 is recognised as a BCO Portal Number')]
    public function testBcoPortalNumberIsRecognisedAsBcoPortalNumber(): void
    {
        $caseIdentifier = new CaseIdentifier('AA1-123-123');

        $this->assertTrue($caseIdentifier->isBcoPortalNumber());
        $this->assertFalse($caseIdentifier->isHpzoneNumber());
        $this->assertFalse($caseIdentifier->isMonsterNumber());
    }

    #[DataProvider('hpzoneNumberProvider')]
    #[TestDox('$hpzoneNumber is recognised as a HPZonenumber')]
    public function testHpzoneNumber(string $hpzoneNumber): void
    {
        $caseIdentifier = new CaseIdentifier($hpzoneNumber);

        $this->assertFalse($caseIdentifier->isBcoPortalNumber());
        $this->assertTrue($caseIdentifier->isHpzoneNumber());
        $this->assertFalse($caseIdentifier->isMonsterNumber());
    }

    public static function hpzoneNumberProvider(): array
    {
        return [
            ['1231231'],
            ['12312312'],
        ];
    }
}
