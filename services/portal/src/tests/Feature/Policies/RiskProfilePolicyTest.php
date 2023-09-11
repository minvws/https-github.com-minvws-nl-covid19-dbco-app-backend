<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\RiskProfile;
use App\Policies\RiskProfilePolicy;
use Generator;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('authorization')]
#[Group('policy')]
class RiskProfilePolicyTest extends FeatureTestCase
{
    private RiskProfilePolicy $riskProfilePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->riskProfilePolicy = $this->app->make(RiskProfilePolicy::class);
    }

    #[DataProvider('policyDataProvider')]
    public function testPolicy(string $role, array $permissions): void
    {
        $user = $this->createUser([], $role);

        $riskProfile = RiskProfile::factory()->create();

        foreach ($permissions as ['method' => $method, 'expectedResult' => $expectedResult]) {
            $this->assertSame(
                $expectedResult,
                $this->riskProfilePolicy->$method($user, $riskProfile),
                "`{$role}` tries to access method `{$method}`",
            );
        }
    }

    public static function policyDataProvider(): Generator
    {
        yield 'With role `user`' => [
            'role' => 'user',
            'permissions' => [
                [
                    'method' => 'viewAny',
                    'expectedResult' => false,
                ],
                [
                    'method' => 'view',
                    'expectedResult' => false,
                ],
                [
                    'method' => 'delete',
                    'expectedResult' => false,
                ],
                [
                    'method' => 'update',
                    'expectedResult' => false,
                ],
            ],
        ];

        yield 'With role `admin`' => [
            'role' => 'admin',
            'permissions' => [
                [
                    'method' => 'viewAny',
                    'expectedResult' => true,
                ],
                [
                    'method' => 'view',
                    'expectedResult' => true,
                ],
                [
                    'method' => 'delete',
                    'expectedResult' => false,
                ],
                [
                    'method' => 'update',
                    'expectedResult' => true,
                ],
            ],
        ];
    }

    public function testPolicyDeleteWithIsActiveSetToFalseForAdmin(): void
    {
        $user = $this->createUser(roles: 'admin');
        $riskProfile = RiskProfile::factory()->create(['is_active' => false]);

        $this->assertTrue(
            $this->riskProfilePolicy->delete($user, $riskProfile),
            '`admin` tries to access method `delete` while risk profile is not active',
        );
    }
}
