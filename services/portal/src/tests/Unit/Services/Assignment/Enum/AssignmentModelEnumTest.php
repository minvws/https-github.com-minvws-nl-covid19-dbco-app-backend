<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Assignment\Enum;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use BackedEnum;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use UnhandledMatchError;

use function count;
use function sprintf;

#[Group('assignment')]
final class AssignmentModelEnumTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $cases = AssignmentModelEnum::cases();

        $this->assertTrue(count($cases) > 0, sprintf('Expected at least 1 case for "%s"', AssignmentModelEnum::class));
        $this->assertInstanceOf(AssignmentModelEnum::class, $cases[0]);
        $this->assertInstanceOf(BackedEnum::class, $cases[0]);
    }

    public function testGetClass(): void
    {
        try {
            foreach (AssignmentModelEnum::cases() as $case) {
                $this->assertIsString($case->getClass());
            }
        } catch (UnhandledMatchError $e) {
            throw new AssertionFailedError(
                sprintf('Missing match case in %s->getClass() for case "%s".', AssignmentModelEnum::class, $case->name),
                $e->getCode(),
                $e,
            );
        }
    }
}
