<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Events\PolicyVersionCreated;
use App\Exceptions\Policy\PolicyVersionUpdateNotAllowedException;
use App\Models\Policy\PolicyVersion;
use App\Services\PolicyVersionService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use LogicException;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function is_null;
use function sprintf;

#[Group('policy')]
#[Group('policyVersion')]
class PolicyVersionServiceTest extends FeatureTestCase
{
    private PolicyVersionService $policyVersionService;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->policyVersionService = $this->app->make(PolicyVersionService::class);
    }

    public function testPolicyVersionServiceUpdateToActiveOnCurrentDay(): void
    {
        $activePolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        $newPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::now(),
        ]);

        $dto = new UpdatePolicyVersionDto(
            name: None::create(),
            status: Some::create(PolicyVersionStatus::active()),
            startDate: Some::create($newPolicyVersion->start_date),
        );

        $this->policyVersionService->updatePolicyVersion($newPolicyVersion, $dto);

        $this->assertEquals(PolicyVersionStatus::active(), $newPolicyVersion->status);
        $this->assertEquals(PolicyVersionStatus::old(), $activePolicyVersion->refresh()->status);
    }

    public function testPolicyVersionServiceUpdateToActiveSoonInFuture(): void
    {
        $activePolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        $newPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::now()->addDays(3),
        ]);

        $dto = new UpdatePolicyVersionDto(
            name: None::create(),
            status: Some::create(PolicyVersionStatus::activeSoon()),
            startDate: Some::create($newPolicyVersion->start_date),
        );

        $this->policyVersionService->updatePolicyVersion($newPolicyVersion, $dto);

        $this->assertEquals(PolicyVersionStatus::activeSoon(), $newPolicyVersion->status);
        $this->assertEquals(
            PolicyVersionStatus::active(),
            $activePolicyVersion->refresh()->status,
        ); // Active PolicyProfile should not change status
    }

    public function testGetActivatablePolicyVersion(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        $newPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::now(),
        ]);

        $activatablePolicyService = $this->policyVersionService->getPolicyVersionReadyForActivation();

        $this->assertEquals($newPolicyVersion->uuid, $activatablePolicyService->uuid);
    }

    public function testGetActivatablePolicyVersionShouldNotFindNothing(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::now()->addDays(3),
        ]);

        /** @var PolicyVersionService $policyVersionService */
        $policyVersionService = $this->app->make(PolicyVersionService::class);
        $this->assertNull($policyVersionService->getPolicyVersionReadyForActivation());
    }

    public function testGetActivatablePolicyVersionShouldThrowException(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());

        $this->expectException(LogicException::class);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::now()->subDays(3),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::now(),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::now(),
        ]);

        $this->policyVersionService->getPolicyVersionReadyForActivation();
    }

    #[DataProvider('getAllowsMutationsData')]
    public function testAllowsMutations(PolicyVersionStatus $status, bool $expectException): void
    {
        $policyVersion = new PolicyVersion(['status' => $status]);

        if ($expectException) {
            $this->expectExceptionObject(PolicyVersionUpdateNotAllowedException::create());

            $this->policyVersionService->allowsMutations($policyVersion);
        } else {
            $this->assertNull($this->policyVersionService->allowsMutations($policyVersion));
        }
    }

    public static function getAllowsMutationsData(): array
    {
        return Collection::make(PolicyVersionStatus::all())
            ->reject(static fn (PolicyVersionStatus $status) => $status === PolicyVersionStatus::draft())
            ->mapWithKeys(static function (PolicyVersionStatus $status): array {
                $key = sprintf('status "%s" should throw exception', $status->value);
                return [$key => ['status' => $status, 'expectException' => true]];
            })
            ->put('status "draft" should not throw exception', ['status' => PolicyVersionStatus::draft(), 'expectException' => false])
            ->toArray();
    }

    public function testGetActivePolicyVersion(): void
    {
        $activePolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::parse('-3 days'),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::parse('+3 days'),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::draft(),
            'start_date' => CarbonImmutable::parse('-1 days'),
        ]);

        /** @var PolicyVersionService $policyVersionService */
        $policyVersionService = $this->app->make(PolicyVersionService::class);
        $this->assertEquals($activePolicyVersion->uuid, $policyVersionService->getActivePolicyVersion()->uuid);
    }

    public function testGetPolicyVersionByDate(): void
    {
        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
            'start_date' => CarbonImmutable::parse('-3 days'),
        ]);

        PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::activeSoon(),
            'start_date' => CarbonImmutable::parse('+3 days'),
        ]);

        $oldPolicyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::old(),
            'start_date' => CarbonImmutable::parse('-10 days'),
        ]);

        /** @var PolicyVersionService $policyVersionService */
        $policyVersionService = $this->app->make(PolicyVersionService::class);
        $this->assertEquals(
            $oldPolicyVersion->uuid,
            $policyVersionService->getPolicyVersionByDate(CarbonImmutable::parse('-5 days'))->uuid,
        );
    }

    #[DataProvider('policyVersionsUseCases')]
    public function testGetPolicyVersionForCase(array $versions, array $caseAttributes, DateTimeInterface $statusChangedAt, string|null $expectedVersion): void
    {
        foreach ($versions as $name => $versionAttributes) {
            $versions[$name] = PolicyVersion::factory()->create($versionAttributes);
        }

        $case = $this->createCase($caseAttributes);
        $this->createCaseStatusHistoryWithStatusForCase($case, $case->bcoStatus, $statusChangedAt);

        /** @var PolicyVersionService $policyVersionService */
        $policyVersionService = $this->app->make(PolicyVersionService::class);
        if (is_null($expectedVersion)) {
            $this->assertNull($policyVersionService->getPolicyVersionForCase($case));
        } else {
            $this->assertEquals(
                $versions[$expectedVersion]->uuid,
                $policyVersionService->getPolicyVersionForCase($case)->uuid,
            );
        }
    }

    public static function policyVersionsUseCases(): Generator
    {
        yield 'with bco_status open' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                ],
            ],
            [
                'bco_status' => BCOStatus::open(),
            ],
            CarbonImmutable::now(),
            'active',
        ];

        yield 'with bco_status completed and without relation' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::completed(),
                'completed_at' => CarbonImmutable::parse('-4 days'),
            ],
            CarbonImmutable::now(),
            'old',
        ];

        yield 'with bco_status completed and without relation and no applicable policyVersion' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::completed(),
                'completed_at' => CarbonImmutable::parse('-10 days'),
            ],
            CarbonImmutable::now(),
            'active',
        ];

        yield 'with bco_status completed and with relation' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
                'relation' => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-10 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::completed(),
                'completed_at' => CarbonImmutable::parse('-4 days'),
                'policy_version_uuid' => '00000000-0000-0000-0000-000000000000',
            ],
            CarbonImmutable::now(),
            'relation',
        ];

        yield 'with bco_status archived and without relation' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::archived(),
                'completed_at' => CarbonImmutable::parse('-4 days'),
            ],
            CarbonImmutable::now(),
            'old',
        ];

        yield 'with bco_status archived and without relation and no applicable policyVersion' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::archived(),
                'completed_at' => CarbonImmutable::parse('-10 days'),
            ],
            CarbonImmutable::now(),
            'active',
        ];

        yield 'with bco_status archived and with relation' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
                'relation' => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-10 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::archived(),
                'completed_at' => CarbonImmutable::parse('-4 days'),
                'policy_version_uuid' => '00000000-0000-0000-0000-000000000000',
            ],
            CarbonImmutable::now(),
            'relation',
        ];

        yield 'with bco_status completed and older then 8 weeks' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
                'relation' => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-10 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::completed(),
                'completed_at' => CarbonImmutable::parse('-9 weeks'),
            ],
            CarbonImmutable::parse('-9 weeks'),
            null,
        ];

        yield 'with bco_status archived and older then 8 weeks' => [
            [
                'draft' => [
                    'status' => PolicyVersionStatus::draft(),
                    'start_date' => CarbonImmutable::parse('+2 days'),
                ],
                'active' => [
                    'status' => PolicyVersionStatus::active(),
                    'start_date' => CarbonImmutable::parse('-3 days'),
                ],
                'old' => [
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-5 days'),
                ],
                'relation' => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'status' => PolicyVersionStatus::old(),
                    'start_date' => CarbonImmutable::parse('-10 days'),
                ],
            ],
            [
                'bco_status' => BCOStatus::archived(),
                'completed_at' => CarbonImmutable::parse('-9 weeks'),
            ],
            CarbonImmutable::parse('-9 weeks'),
            null,
        ];
    }
}
