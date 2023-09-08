<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Policies\PolicyGuidelinePolicy;
use Generator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('authorization')]
#[Group('policy')]
class PolicyGuidelinePolicyTest extends FeatureTestCase
{
    private PolicyGuidelinePolicy $policyGuidelinePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policyGuidelinePolicy = $this->app->make(PolicyGuidelinePolicy::class);
    }

    #[DataProvider('policyDataProvider')]
    public function testPolicy(string $role, array $permissions): void
    {
        $user = $this->createUser([], $role);

        $policyGuideline = PolicyGuideline::factory()->create();

        foreach ($permissions as ['method' => $method, 'expectedResult' => $expectedResult]) {
            $this->assertSame(
                $expectedResult,
                $this->policyGuidelinePolicy->$method($user, $policyGuideline),
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
                    'expectedResult' => true,
                ],
                [
                    'method' => 'update',
                    'expectedResult' => true,
                ],
            ],
        ];
    }

    public function testPolicyDeleteWhenRiskProfilesAreLinked(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $user = $this->createUser(roles: 'admin');

        $policyVersion = PolicyVersion::factory()->create();
        $policyGuideline = PolicyGuideline::factory()->recycle($policyVersion)->create();
        RiskProfile::factory()->recycle($policyVersion, $policyGuideline)->create([
            'person_type_enum' => PolicyPersonType::index(),
        ]);

        $this->assertFalse(
            $this->policyGuidelinePolicy->delete($user, $policyGuideline),
            '`admin` tries to access method `delete` with linked riskProfiles',
        );
    }
}
