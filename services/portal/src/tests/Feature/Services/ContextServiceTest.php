<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\CovidCase;
use App\Services\ContextService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\ContextCategorySuggestionGroup;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function collect;
use function sprintf;

use const PHP_EOL;

class ContextServiceTest extends FeatureTestCase
{
    public function testGetContextsForCase(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $contextService = $this->app->get(ContextService::class);

        $contexts = $contextService->getContextsForCase($case);

        $this->assertCount(1, $contexts);
        $this->assertEquals($context->uuid, $contexts->first()->uuid);
    }

    public function testGetContextsForCaseWithPlaceDataIncluded(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $place = $this->createPlace();
        $context = $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);
        $contextService = $this->app->get(ContextService::class);

        $contexts = $contextService->getContextsForCase($case, null, true);

        $this->assertCount(1, $contexts);
        $this->assertEquals($context->uuid, $contexts->first()->uuid);
        $this->assertEquals($place->uuid, $contexts->first()->place->uuid);
    }

    public function testCountContextsInContagiousPeriod(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $this->createMomentForContext($context, [
            'day' => $case->date_of_test->format('Y-m-d'),
        ]);

        // Should not be included
        $this->createMomentForContext($this->createContextForCase($case), [
            'day' => $case->date_of_test->subDays()->format('Y-m-d'),
        ]);

        $contextService = $this->app->get(ContextService::class);

        $contexts = $contextService->getContextsForCase($case, 'contagious');

        $this->assertCount(1, $contexts);
        $this->assertEquals($context->uuid, $contexts->first()->uuid);
    }

    public function testUpdateContextWithoutValues(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);

        $contextService = $this->app->get(ContextService::class);
        $contextService->updateContext($context, null, null, null, null, null, null, null, true, null);

        $this->assertDatabaseHas('context', [
            'uuid' => $context->uuid,
            'covidcase_uuid' => $case->uuid,
            'label' => null,
            'place_uuid' => null,
            'relationship' => null,
            'other_relationship' => null,
            'explanation' => null,
            'detailed_explanation' => null,
            'remarks' => null,
            'is_source' => 1,
        ]);

        $this->assertDatabaseMissing('moment', [
            'context_uuid' => $context->uuid,
        ]);
    }

    public function testUpdateContextWithValues(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $oldPlace = $this->createPlace();
        $context = $this->createContextForCase($case, [
            'place_uuid' => $oldPlace->uuid,
        ]);

        $covidCase = new CovidCase();
        $covidCase->uuid = $case->uuid;
        $newPlace = $this->createPlace();

        $contextService = $this->app->get(ContextService::class);
        $contextService->updateContext(
            $context,
            'label',
            $newPlace->uuid,
            ContextRelationship::patient(),
            'otherRelation',
            'some explanation',
            'some other explanation',
            'remarks',
            true,
            ['2020-01-01'],
            $covidCase,
        );

        $this->assertDatabaseHas('context', [
            'uuid' => $context->uuid,
            'covidcase_uuid' => $covidCase->uuid,
            'label' => 'label',
            'place_uuid' => $newPlace->uuid,
            'relationship' => 'patient',
            'other_relationship' => 'otherRelation',
            'explanation' => 'some explanation',
            'detailed_explanation' => 'some other explanation',
            'remarks' => 'remarks',
            'is_source' => 1,
        ]);

        $this->assertDatabaseHas('moment', [
            'context_uuid' => $context->uuid,
            'day' => '2020-01-01',
        ]);
    }

    #[DataProvider('updateContextExplanationDataProvider')]
    public function testLinkPlaceToContextWithoutExplanationShouldSetSuggestions(
        ?string $existingExplanation,
        string $expectedExplanation,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case, [
            'place_uuid' => null,
            'explanation' => $existingExplanation,
        ]);

        $newPlace = $this->createPlace([
            'category' => ContextCategory::verenigingOverige(),
        ]);

        $contextService = $this->app->get(ContextService::class);
        $contextService->linkPlaceToContext($context, $newPlace);

        $this->assertDatabaseHas('context', [
            'uuid' => $context->uuid,
            'place_uuid' => $newPlace->uuid,
            'explanation' => $expectedExplanation,
        ]);
    }

    public function testLinkPlaceToContextWillSetPlaceAddedAtTimestamp(): void
    {
        $fakerDateTime = $this->faker->dateTimeBetween;
        CarbonImmutable::setTestNow($fakerDateTime);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case, [
            'place_uuid' => null,
        ]);

        $place = $this->createPlace();

        $contextService = $this->app->get(ContextService::class);
        $contextService->linkPlaceToContext($context, $place);

        $this->assertDatabaseHas('context', [
            'uuid' => $context->uuid,
            'place_uuid' => $place->uuid,
            'place_added_at' => $fakerDateTime,
        ]);
    }

    public function testUnlinkPlaceToContextWillSetPlaceAddedAtNull(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $place = $this->createPlace();

        $context = $this->createContextForCase($case, [
            'place_uuid' => $place,
            'place_added_at' => $this->faker->dateTime(),
        ]);

        $contextService = $this->app->get(ContextService::class);
        $contextService->unlinkPlaceFromContext($context, $place);

        $this->assertDatabaseHas('context', [
            'uuid' => $context->uuid,
            'place_added_at' => null,
        ]);
    }

    public static function updateContextExplanationDataProvider(): array
    {
        $suggestion = collect(ContextCategorySuggestionGroup::verenigingOverige()->suggestions)
            ->values()
            ->implode(sprintf('%s%s%s', PHP_EOL, PHP_EOL, PHP_EOL));

        return [
            'null' => [null, $suggestion],
            'empty string' => ['', $suggestion],
            'existing' => ['some existing explanation', 'some existing explanation'],
        ];
    }
}
