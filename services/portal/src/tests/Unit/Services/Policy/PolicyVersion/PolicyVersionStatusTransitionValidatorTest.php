<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Policy\PolicyVersion;

use App\Services\Policy\PolicyVersion\PolicyVersionStatusTransitionValidator;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('policy')]
#[Group('policyVersion')]
class PolicyVersionStatusTransitionValidatorTest extends TestCase
{
    #[DataProvider('statusTransitionsOnCurrentDayProvider')]
    public function testStatusTransitionOnFutureDay(
        PolicyVersionStatus $fromStatus,
        PolicyVersionStatus $toStatus,
        bool $expectedResult,
    ): void
    {
        $policyVersionStatusValidator = new PolicyVersionStatusTransitionValidator();
        $this->assertEquals($expectedResult, $policyVersionStatusValidator->isValid($fromStatus, $toStatus, CarbonImmutable::now()));
    }

    #[DataProvider('statusTransitionsOnFutureDayProvider')]
    public function testStatusTransitionOnCurrentDay(
        PolicyVersionStatus $fromStatus,
        PolicyVersionStatus $toStatus,
        bool $expectedResult,
    ): void
    {
        $policyVersionStatusValidator = new PolicyVersionStatusTransitionValidator();
        $this->assertEquals($expectedResult, $policyVersionStatusValidator->isValid($fromStatus, $toStatus, CarbonImmutable::now()
            ->addDays(3)));
    }

    public static function statusTransitionsOnCurrentDayProvider(): array
    {
        return [
            'draft to active' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::active(),
                'expectedResult' => true,
            ],
            'draft to active-soon' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::activeSoon(),
                'expectedResult' => false,
            ],
            'draft to old' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::old(),
                'expectedResult' => false,
            ],
        ];
    }

    public static function statusTransitionsOnFutureDayProvider(): array
    {
        return [
            'draft to active-soon' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::activeSoon(),
                'expectedResult' => true,
            ],
            'active-soon to draft' => [
                'fromStatus' => PolicyVersionStatus::activeSoon(),
                'toStatus' => PolicyVersionStatus::draft(),
                'expectedResult' => true,
            ],
            'draft to active' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::active(),
                'expectedResult' => false,
            ],
            'draft to old' => [
                'fromStatus' => PolicyVersionStatus::draft(),
                'toStatus' => PolicyVersionStatus::old(),
                'expectedResult' => false,
            ],
            'old to draft' => [
                'fromStatus' => PolicyVersionStatus::old(),
                'toStatus' => PolicyVersionStatus::draft(),
                'expectedResult' => false,
            ],
        ];
    }
}
