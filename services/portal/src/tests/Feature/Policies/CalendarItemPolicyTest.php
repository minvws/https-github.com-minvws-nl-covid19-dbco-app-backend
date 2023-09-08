<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Policies\CalendarItemPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('authorization')]
#[Group('policy')]
#[Group('calendarItem')]
final class CalendarItemPolicyTest extends FeatureTestCase
{
    private CalendarItemPolicy $calendarItemPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarItemPolicy = $this->app->make(CalendarItemPolicy::class);
    }

    #[DataProvider('policyUserDataProvider')]
    #[DataProvider('policyAdminDataProvider')]
    #[TestDox('With role "$role" and method "$method"')]
    public function testPolicy(string $role, string $method, bool $expectedResult): void
    {
        $user = $this->createUser([], $role);

        $this->assertSame(
            $expectedResult,
            $this->calendarItemPolicy->$method($user)->allowed(),
            sprintf('Role "%s" tries to access method "%s"', $role, $method),
        );
    }

    public static function policyUserDataProvider(): array
    {
        return [
            [
                'role' => 'user',
                'method' => 'viewAny',
                'expectedResult' => false,
            ],
            [
                'role' => 'user',
                'method' => 'view',
                'expectedResult' => false,
            ],
            [
                'role' => 'user',
                'method' => 'delete',
                'expectedResult' => false,
            ],
            [
                'role' => 'user',
                'method' => 'create',
                'expectedResult' => false,
            ],
            [
                'role' => 'user',
                'method' => 'update',
                'expectedResult' => false,
            ],
        ];
    }

    public static function policyAdminDataProvider(): array
    {
        return [
            [
                'role' => 'admin',
                'method' => 'viewAny',
                'expectedResult' => true,
            ],
            [
                'role' => 'admin',
                'method' => 'view',
                'expectedResult' => true,
            ],
            [
                'role' => 'admin',
                'method' => 'delete',
                'expectedResult' => true,
            ],
            [
                'role' => 'admin',
                'method' => 'create',
                'expectedResult' => true,
            ],
            [
                'role' => 'admin',
                'method' => 'update',
                'expectedResult' => true,
            ],
        ];
    }
}
