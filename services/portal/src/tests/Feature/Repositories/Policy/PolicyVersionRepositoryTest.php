<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Dto\Admin\CreatePolicyVersionDto;
use App\Dto\Admin\UpdatePolicyVersionDto;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\PolicyVersionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use Mockery;
use Mockery\MockInterface;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('policyVersion')]
class PolicyVersionRepositoryTest extends FeatureTestCase
{
    private PolicyVersionRepository $policyVersionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->policyVersionRepository = $this->app->make(PolicyVersionRepository::class);
    }

    public function testGetPolicyVersions(): void
    {
        $now = CarbonImmutable::now()->roundSeconds();

        $excpectedVersions = Collection::make([
            [
                'status' => PolicyVersionStatus::draft(),
                'start_date' => $now->addDays(3),
            ],
            [
                'status' => PolicyVersionStatus::draft(),
                'start_date' => $now->addDays(2),
            ],
            [
                'status' => PolicyVersionStatus::draft(),
                'start_date' => $now->addDays(1),
            ],
            [
                'status' => PolicyVersionStatus::activeSoon(),
                'start_date' => $now->addDays(2),
            ],
            [
                'status' => PolicyVersionStatus::activeSoon(),
                'start_date' => $now->addDays(1),
            ],
            [
                'status' => PolicyVersionStatus::active(),
                'start_date' => $now,
            ],
            [
                'status' => PolicyVersionStatus::old(),
                'start_date' => $now->subDays(1),
            ],
            [
                'status' => PolicyVersionStatus::old(),
                'start_date' => $now->subDays(2),
            ],
            [
                'status' => PolicyVersionStatus::old(),
                'start_date' => $now->subDays(3),
            ],
        ]);

        $excpectedVersions
            ->shuffle()
            ->map(static fn (array $data): string => PolicyVersion::factory()->create($data)->uuid);

        $actualPolicyVersions = $this->policyVersionRepository->getPolicyVersions();

        $this->assertCount(9, $actualPolicyVersions);

        $this->assertEquals(
            $excpectedVersions,
            $actualPolicyVersions->map(static fn (PolicyVersion $policyVersion): array => [
                'status' => $policyVersion->status,
                'start_date' => $policyVersion->start_date,
            ]),
        );
    }

    public function testDeletePolicyVersion(): void
    {
        $policyVersion1 = PolicyVersion::factory()->create();
        $policyVersion2 = PolicyVersion::factory()->create();

        $this->policyVersionRepository->deletePolicyVersion($policyVersion1);

        $this->assertDatabaseMissing(PolicyVersion::class, ['uuid' => $policyVersion1->uuid]);
        $this->assertDatabaseHas(PolicyVersion::class, ['uuid' => $policyVersion2->uuid]);
    }

    public function testDeletePolicyVersionReturnsFalseOnLogicException(): void
    {
        $policyVersionWithoutPrimaryKeyDefined = (new PolicyVersion())->setKeyName(null);

        $this->assertFalse($this->policyVersionRepository->deletePolicyVersion($policyVersionWithoutPrimaryKeyDefined));
    }

    public function testDeletePolicyVersionReturnsFalseWhenModelDoesNotExist(): void
    {
        $policyVersion = new PolicyVersion();

        $this->assertFalse($this->policyVersionRepository->deletePolicyVersion($policyVersion));
    }

    public function testCreatePolicyVersion(): void
    {
        $dto = new CreatePolicyVersionDto(
            name: $this->faker->words(asText: true),
            startDate: CarbonImmutable::instance($this->faker->dateTimeBetween('now', '+1 year')),
        );

        $this->assertDatabaseCount('policy_version', 0);

        $policyVersion = PolicyVersion::factory()->create($dto->toEloquentAttributes());

        $this->assertDatabaseCount('policy_version', 1);
        $this->assertDatabaseHas('policy_version', ['uuid' => $policyVersion->uuid]);
    }

    public function testUpdatePolicyVersionWithUpdatingStartData(): void
    {
        $startDate = $this->faker->dateTimeBetween('+2 years', '+3 years');
        $updatedName = $this->faker->name();

        $policyVersion = PolicyVersion::factory()->create([
            'name' => $this->faker->word,
            'status' => PolicyVersionStatus::draft(),
            'start_date' => $startDate,
        ]);

        $dto = new UpdatePolicyVersionDto(
            new Some($updatedName),
            new Some(PolicyVersionStatus::activeSoon()),
            None::create(),
        );

        $updatedPolicyVersion = $this->policyVersionRepository->updatePolicyVersion($policyVersion, $dto);

        $this->assertSame($policyVersion, $updatedPolicyVersion);

        $this->assertEquals($updatedName, $updatedPolicyVersion->name);
        $this->assertEquals(PolicyVersionStatus::activeSoon()->value, $updatedPolicyVersion->status);
        $this->assertEquals($startDate, $updatedPolicyVersion->start_date);
    }

    public function testUpdatePolicyVersionWithoutUpdatingName(): void
    {
        $name = $this->faker->word;
        $startDate = $this->faker->dateTimeBetween('+2 years', '+3 years');

        $policyVersion = PolicyVersion::factory()->create([
            'name' => $name,
            'status' => PolicyVersionStatus::draft(),
            'start_date' => $startDate,
        ]);

        $dto = new UpdatePolicyVersionDto(
            None::create(),
            new Some(PolicyVersionStatus::activeSoon()),
            None::create(),
        );

        $updatedPolicyVersion = $this->policyVersionRepository->updatePolicyVersion($policyVersion, $dto);

        $this->assertSame($policyVersion, $updatedPolicyVersion);

        $this->assertEquals($name, $updatedPolicyVersion->name);
        $this->assertEquals(PolicyVersionStatus::activeSoon()->value, $updatedPolicyVersion->status);
        $this->assertEquals($startDate, $updatedPolicyVersion->start_date);
    }

    public function testUpdatePolicyVersionThrowingExceptionOnFailedUpdate(): void
    {
        /** @var PolicyVersion&MockInterface $policyVersion */
        $policyVersion = Mockery::mock(PolicyVersion::class);
        $policyVersion->shouldReceive('getAttribute')->with('uuid')->andReturn($this->faker->uuid);
        $policyVersion->shouldReceive('update')->once()->andReturnFalse();

        /** @var UpdatePolicyVersionDto&MockInterface $dto */
        $dto = Mockery::mock(UpdatePolicyVersionDto::class);
        $dto->shouldReceive('toArray')->once()->andReturn([]);

        $this->expectExceptionObject(
            new RuntimeException(sprintf('Failed to update policy version with UUID: "%s"', $policyVersion->uuid)),
        );

        $this->policyVersionRepository->updatePolicyVersion($policyVersion, $dto);
    }
}
