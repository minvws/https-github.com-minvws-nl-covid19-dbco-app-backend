<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\PolicyGuidelineRepository;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('policyGuideline')]
class PolicyGuidelineRepositoryTest extends FeatureTestCase
{
    public function testPopulatedPolicyGuidelines(): void
    {
        /** @var PolicyGuidelineRepository $policyGuidelineRepository */
        $policyGuidelineRepository = $this->app->make(PolicyGuidelineRepository::class);

        //Trigger population of policy tables
        $policyGuidelines = $policyGuidelineRepository->getPolicyGuidelinesByPolicyVersion(PolicyVersion::factory()->create());

        $this->assertCount(4, $policyGuidelines);
        $this->assertDatabaseHas(PolicyGuideline::class, ['identifier' => 'symptomatic_extended']);
    }

    public function testPopulatedPolicyGuidelinesByIdentifier(): void
    {
        /** @var PolicyGuidelineRepository $policyGuidelineRepository */
        $policyGuidelineRepository = $this->app->make(PolicyGuidelineRepository::class);

        //Trigger population of policy tables
        $policyGuideline = $policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
            'symptomatic_extended',
            PolicyVersion::factory()->create(),
        );

        $this->assertInstanceOf(PolicyGuideline::class, $policyGuideline);
        $this->assertDatabaseHas(PolicyGuideline::class, ['identifier' => 'symptomatic_extended']);
    }

    public function testUpdatePolicyGuidelines(): void
    {
        /** @var PolicyGuidelineRepository $policyGuidelineRepository */
        $policyGuidelineRepository = $this->app->make(PolicyGuidelineRepository::class);

        $policyGuideline = PolicyGuideline::factory()->create();

        $attributes = [
            'name' => $this->faker->word,
            'sourceStartDateReference' => PolicyGuidelineReferenceField::dateOfTest()->value,
            'sourceStartDateAddition' => $this->faker->numberBetween(-10, 10),
            'contagiousEndDateReference' => PolicyGuidelineReferenceField::dateOfSymptomOnset()->value,
            'contagiousEndDateAddition' => $this->faker->numberBetween(-10, 10),
        ];

        $policyGuidelineRepository->updatePolicyGuideline($policyGuideline, $attributes);

        $this->assertDatabaseHas(PolicyGuideline::class, [
            'uuid' => $policyGuideline->uuid,
            'name' => $attributes['name'],
            'source_start_date_reference' => $attributes['sourceStartDateReference'],
            'source_start_date_addition' => $attributes['sourceStartDateAddition'],
            'contagious_end_date_reference' => $attributes['contagiousEndDateReference'],
            'contagious_end_date_addition' => $attributes['contagiousEndDateAddition'],
        ]);
    }

    public function testLoadMissing(): void
    {
        /** @var PolicyGuidelineRepository $policyGuidelineRepository */
        $policyGuidelineRepository = $this->app->make(PolicyGuidelineRepository::class);

        $policyGuideline = PolicyGuideline::factory()->make();
        $relations = ['foobar'];

        /** @var PolicyGuideline&MockInterface $policyGuidelineMock */
        $policyGuidelineMock = Mockery::mock(PolicyGuideline::class);
        $policyGuidelineMock
            ->shouldReceive('loadMissing')
            ->with(...$relations)
            ->once()
            ->andReturn($policyGuideline);

        $policyGuidelineRepository->loadMissing($policyGuidelineMock, ...$relations);
    }
}
