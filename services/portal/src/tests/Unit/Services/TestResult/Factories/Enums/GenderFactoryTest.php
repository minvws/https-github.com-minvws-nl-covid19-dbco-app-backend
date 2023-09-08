<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult\Factories\Enums;

use App\Dto\TestResultReport\Gender;
use App\Services\TestResult\Factories\Enums\GenderFactory;
use LogicException;
use MinVWS\DBCO\Enum\Models\Gender as GenderEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

final class GenderFactoryTest extends UnitTestCase
{
    #[DataProvider('dpCreate')]
    public function testCreate(Gender $gender, GenderEnum $expected): void
    {
        $actual = GenderFactory::create($gender);
        $this->assertEquals($expected, $actual);
    }

    public static function dpCreate(): array
    {
        return [
            [new Gender('MAN'), GenderEnum::male()],
            [new Gender('VROUW'), GenderEnum::female()],
            [new Gender('NIET_GESPECIFICEERD'), GenderEnum::other()],
            [new Gender('ONBEKEND'), GenderEnum::other()],
        ];
    }

    public function testCreateWithInvalidValue(): void
    {
        $this->expectException(LogicException::class);

        $gender = $this->createMock(Gender::class);
        $gender->expects($this->once())->method('isMale')->willReturn(false);
        $gender->expects($this->once())->method('isFemale')->willReturn(false);
        $gender->expects($this->once())->method('isUnknown')->willReturn(false);
        $gender->expects($this->once())->method('isNotSpecified')->willReturn(false);

        GenderFactory::create($gender);
    }
}
