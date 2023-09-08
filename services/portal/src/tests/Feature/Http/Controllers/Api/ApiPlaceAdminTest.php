<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentSituation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Place;
use App\Models\OrganisationType;
use App\Models\Place\PlaceSource;
use App\Services\AuthenticationService;
use App\Services\PlaceService;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\ContextCategoryGroup;
use MinVWS\DBCO\Enum\Models\ContextListView;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function app;
use function array_filter;
use function array_map;
use function array_merge;
use function count;
use function http_build_query;
use function sprintf;

#[Group('place')]
class ApiPlaceAdminTest extends FeatureTestCase
{
    #[DataProvider('placesListProvider')]
    public function testPlacesList(string $view, int $casesTotal, int $expectedCount, ContextCategory $category): void
    {
        $organisation = $this->seedDb($casesTotal, 2, ['category' => $category]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $this->be($user);
        $query = $view ? "?view={$view}" : "";
        $response = $this->getJson("/api/places/search/similar{$query}");

        $this->assertStatus($response, 200);
        $this->assertCount($expectedCount, $response->json('data'));
        if ($expectedCount === 0) {
            return;
        }
        $json = $response->json('data');
        $offset = $expectedCount - 1;
        $this->assertTrue(CarbonImmutable::parse($json[0]['createdAt']) >= CarbonImmutable::parse($json[$offset]['createdAt']));
        $this->assertTrue($json[0]['isVerified']);
    }

    public function testPlacesListByPost(): void
    {
        $organisation = $this->seedDb(1, 2, ['category' => ContextCategory::begeleid()]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $response = $this->be($user)->postJson(sprintf('/api/places/search/similar?view=%s', ContextListView::zorg()));

        $this->assertStatus($response, 200);
        $this->assertCount(2, $response->json('data'));
        $json = $response->json('data');
        $offset = 0;
        $this->assertTrue(CarbonImmutable::parse($json[0]['createdAt']) >= CarbonImmutable::parse($json[$offset]['createdAt']));
        $this->assertTrue($json[0]['isVerified']);
    }

    public static function placesListProvider(): array
    {
        return [
            "full within page length" => ["", 5, 10, ContextCategory::begeleid()],
            "full max page length " => ["", 30, 20, ContextCategory::begeleid()],
            "zorg" => [ContextListView::zorg()->value, 5, 10, ContextCategory::begeleid()],
            "onderwijs" => [ContextListView::onderwijs()->value, 5, 10, ContextCategory::hboUniversiteit()],
            "onderwijs, but with zorg category" => [ContextListView::onderwijs()->value, 5, 0, ContextCategory::begeleid()],
        ];
    }

    #[DataProvider('placesViewAndCategoryGroupFiltersProvider')]
    public function testSearchSimilarPlacesViewAndCategoryGroupFilters(
        ?string $view,
        ?string $categoryGroup,
        int $expectedCount,
    ): void {
        $organisation = $this->createOrganisation();
        $categories = [
            ContextCategory::hboUniversiteit(), // onderwijs
            ContextCategory::verpleeghuis(), // vvt
            ContextCategory::instelling(), // vvt
            ContextCategory::feest(), // thuis
            ContextCategory::bezoek(), // thuis
            ContextCategory::thuis(), // thuis
        ];
        foreach ($categories as $category) {
            $case = $this->createCaseForOrganisation($organisation);
            $this->createContextsForCase(1, $case, ['category' => $category]);
        }
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $query = [
            'perPage' => 20,
            'page' => 1,
            'view' => $view ?? '',
            'categoryGroup' => $categoryGroup ?? '',
        ];
        $response = $this->getJson('/api/places/search/similar?' . http_build_query(array_filter($query)));

        $this->assertStatus($response, 200);
        $this->assertCount($expectedCount, $response->json('data'));
    }

    public static function placesViewAndCategoryGroupFiltersProvider(): Generator
    {
        yield "only 'onderwijs' view, no category group" => [
            ContextListView::onderwijs()->value,
            null,
            1,
        ];
        yield "only 'zorg' view, no category group" => [
            ContextListView::zorg()->value,
            null,
            2,
        ];
        yield "only 'overig' view, no category group" => [
            ContextListView::overig()->value,
            null,
            3,
        ];
        yield "only 'onderwijs' category group, no view" => [
            null,
            ContextCategoryGroup::onderwijs()->value,
            1,
        ];
        yield "only 'thuis' category group, no view" => [
            null,
            ContextCategoryGroup::thuis()->value,
            3,
        ];
        yield "both view and category group" => [
            ContextListView::zorg()->value,
            ContextCategoryGroup::vvt()->value,
            2,
        ];
    }

    public function testSearchSimilarPlacesIncompatibleViewAndCategoryGroupFilters(): void
    {
        $organisation = $this->seedDb(1, 1, ['category' => ContextCategory::overig()]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $query = [
            'perPage' => 20,
            'page' => 1,
            'view' => ContextListView::zorg()->value,
            'categoryGroup' => ContextCategoryGroup::overig()->value,
        ];
        $response = $this->getJson('/api/places/search/similar?' . http_build_query(array_filter($query)));
        $this->assertStatus($response, 500);
    }

    #[DataProvider('filteringAndPaginationProvider')]
    public function testFilteringAndPagination(?int $perPage, ?int $page, int $casesTotal, int $contextsPerCase, int $expectedCount): void
    {
        $organisation = $this->seedDb($casesTotal, $contextsPerCase);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $query = [];
        if ($perPage !== null) {
            $query['perPage'] = $perPage;
        }
        if ($page !== null) {
            $query['page'] = $page;
        }

        $queryString = count($query) > 0 ? "?" . http_build_query($query) : "";

        $this->be($user);
        $response = $this->getJson("/api/places/search/similar" . $queryString);
        $this->assertStatus($response, 200);
        $json = $response->json();
        $this->assertCount($expectedCount, $json['data']);
    }

    public static function filteringAndPaginationProvider(): array
    {
        return [
            'full 1st page count' => [15, 1, 30, 2, 15],
            'full 2nd page count' => [30, 2, 15, 3, 15],
        ];
    }

    private function createContextsForCase(int $intContextsToCreate, EloquentCase $case, array $placeAttributes = []): void
    {
        for ($j = 1; $j <= $intContextsToCreate; $j++) {
            $this->createContextWithNewPlace($case, array_merge([
                'location_id' => $j % 2 ? '123' : null,
                'is_verified' => (bool) $j % 2,
            ], $placeAttributes));
        }
    }

    private function createContextWithNewPlace(EloquentCase $case, array $placeAttributes = []): Context
    {
        $place = $this->createPlace($placeAttributes);
        $context = $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        $placeService = app(PlaceService::class);
        $placeService->setPlaceOrganisationFromCase($place, $context->case);

        return $context;
    }

    /**
     * @param array $placeAttributes
     */
    private function createContextWithExistingPlace(EloquentCase $case, Place $place, array $attributes = []): Context
    {
        $context = $this->createContextForCase($case, array_merge([
            'place_uuid' => $place->uuid,
        ], $attributes));

        $placeService = app(PlaceService::class);
        $placeService->setPlaceOrganisationFromCase($place, $context->case);

        return $context;
    }

    /**
     * Populate db with a fixed number of cases and contexts (with linked place)
     */
    private function seedDb(
        int $intCasesToCreate,
        int $intContextsToCreate,
        array $placeAttributes = [],
    ): EloquentOrganisation {
        $organisation = EloquentOrganisation::find('00000000-0000-0000-0000-000000000000');

        for ($i = 0; $i < $intCasesToCreate; $i++) {
            $case = $this->createCaseForOrganisation($organisation, [
                'case_id' => sprintf("%'.07d", $i),
            ]);
            $this->createContextsForCase($intContextsToCreate, $case, $placeAttributes);
        }

        return $organisation;
    }

    public function testPlacesListWithMultipleConnectedPlacesShouldOnlyReturn1Place(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $case = $this->createCaseForUser($user);
        $context = $this->createContextWithNewPlace($case);
        $this->createContextWithExistingPlace($case, $context->place);

        $response = $this->getJson("/api/places/search/similar");

        $this->assertStatus($response, 200);
        $this->assertEquals(1, count($response->json('data')));
    }

    public function testPlaceEncoder(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $case = $this->createCaseForOrganisation($organisation);
        $this->createContextWithNewPlace($case, [
            'label' => 'Golfclub Brunssummerheide',
            'category' => ContextCategory::verenigingOverige(),
            'street' => 'Rimburgerweg',
            'housenumber' => '50',
            'housenumber_suffix' => 'a',
            'postalcode' => '6445PA',
            'town' => 'Brunssum',
            'is_verified' => false,
            'location_id' => '123',
        ]);

        $this->be($user);
        $response = $this->getJson("/api/places/search/similar");

        $this->assertStatus($response, 200);
        $data = $response->json('data');
        $this->assertEquals('Golfclub Brunssummerheide', $data[0]['label']);
        $this->assertEquals('Rimburgerweg', $data[0]['address']['street']);
        $this->assertEquals('Rimburgerweg 50 a, 6445PA Brunssum', $data[0]['addressLabel']);
        $this->assertEquals(false, $data[0]['isVerified']);
        $this->assertEquals(PlaceSource::external(), $data[0]['source']);
    }

    #[DataProvider('placesSearchProvider')]
    public function testSearchSimilarPlaces(string $searchKeys, array $expectedPlaces): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $case = $this->createCaseForOrganisation($organisation);
        $this->createContextWithNewPlace($case, [
            'uuid' => 'uuid1',
            'label' => 'label1',
            'street' => 'street1',
            'postalcode' => '1111AA',
            'town' => 'town1',
        ]);
        $this->createContextWithNewPlace($case, [
            'uuid' => 'uuid2',
            'label' => 'label2',
            'street' => 'street2',
            'postalcode' => '2222AA',
            'town' => 'town2',
        ]);

        $this->be($user);
        $response = $this->getJson("/api/places/search/similar?" . http_build_query(['query' => $searchKeys]));

        $this->assertStatus($response, 200);
        $data = $response->json('data');
        $this->assertEquals(count($expectedPlaces), count($data));
        $this->assertEqualsCanonicalizing(array_map(static fn ($p) => $p['uuid'], $data), $expectedPlaces);
    }

    public static function placesSearchProvider(): array
    {
        return [
            'Multiple results on 1 label key' =>
            [
                'label',
                ['uuid1', 'uuid2'],
            ],
            'One result on 1 label key ' =>
            [
                'label1',
                ['uuid1'],
            ],
            'One result on 1 street key' =>
            [
                'street1',
                ['uuid1'],
            ],
            'One result on 1 postalcode key ' =>
            [
                '1111AA',
                ['uuid1'],
            ],
            'One result on 1 partial postalcode key ' =>
            [
                '1111',
                ['uuid1'],
            ],
            'One result on 1 town key' =>
            [
                'town1',
                ['uuid1'],
            ],
            'Multiple results on `label street` searchkeys' =>
            [
                'label street',
                ['uuid1', 'uuid2'],
            ],
            'No result on `label1 street2` searchkeys' =>
            [
                'label1 street2',
                [],
            ],
            'One result on 1 `label1 street1` searchkeys' =>
            [
                'label1 street1',
                ['uuid1'],
            ],
            'No result on 1 label key and 1 unknown key' =>
            [
                'label1 unknown',
                [],
            ],
        ];
    }

    public function testGetPlacesIncludeIndexCountWhenNotResetIsKnown(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $case = $this->createCaseForOrganisation($organisation);
        $place = $this->createPlace();

        $this->createContextWithExistingPlace($case, $place);

        $anotherCase = $this->createCaseForOrganisation($organisation);
        $this->createContextWithExistingPlace($anotherCase, $place);
        $this->be($user);

        $response = $this->getJson("/api/places/search/similar");

        $this->assertStatus($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(2, $data[0]['indexCountSinceReset']);
        $this->assertNull($data[0]['indexCountResetAt']);
    }

    public function testResetCountForPlacePasses(): void
    {
        $fakerDateTime = $this->faker->dateTime();
        CarbonImmutable::setTestNow($fakerDateTime);

        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $this->createCaseForOrganisation($organisation);
        $place = $this->createPlaceForOrganisation($organisation, [
            'index_count_reset_at' => $this->faker->dateTime(),
        ]);

        $response = $this->postJson("/api/places/{$place->uuid}/cluster/reset");
        $response->assertStatus(200);

        $this->assertDatabaseHas('place', [
            'index_count_reset_at' => $fakerDateTime,
        ]);
    }

    public function testResetCountForPlaceReturnNewResetTime(): void
    {
        $fakerDateTime = $this->faker->dateTime();
        CarbonImmutable::setTestNow($fakerDateTime);

        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $this->createCaseForOrganisation($organisation);
        $place = $this->createPlaceForOrganisation($organisation, [
            'index_count_reset_at' => $fakerDateTime,
        ]);

        $this->postJson("/api/places/{$place->uuid}/cluster/reset");
        $this->assertDatabaseHas('place', [
            'index_count_reset_at' => $fakerDateTime,
        ]);
    }

    public function testGetPlacesIncludeIndexCountWhenResetIsKnown(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        $case = $this->createCaseForOrganisation($organisation);
        $place = $this->createPlace([
            'index_count_reset_at' => $resetTime = CarbonImmutable::now(),
        ]);

        $this->createContextWithExistingPlace($case, $place, [
            // Should be earlier then reset time
            'place_added_at' => CarbonImmutable::now()->subHour(),
        ]);

        $anotherCase = $this->createCaseForOrganisation($organisation);
        $this->createContextWithExistingPlace($anotherCase, $place, [
            // Should be later than reset time
            'place_added_at' => CarbonImmutable::now()->addHour(),
        ]);

        $this->be($user);

        $response = $this->getJson("/api/places/search/similar");

        $this->assertStatus($response, 200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(1, $data[0]['indexCountSinceReset']);
        $this->assertEquals($resetTime, $data[0]['indexCountResetAt']);
    }

    public function testPlaceLastIndexPresenceShouldAlwaysListLastMoment(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $case = $this->createCaseForUser($user);
        $place = $this->createPlaceForOrganisation($organisation);
        $context = $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        $now = CarbonImmutable::now();

        // The moment that should give back the last presence values
        $moment = $this->createMomentForContext($context, [
            'day' => $now->format('Y-m-d'),
            'end_time' => $now->format('h:i'),
        ]);

        // Additional moments to make sure the right one will be used
        $this->createMomentForContext($context, [
            'day' => $now->subHour()->format('Y-m-d'),
            'end_time' => $now->subHour()->format('h:i'),
        ]);

        // Additional moments to make sure the right one will be used
        $this->createMomentForContext($context, [
            'day' => $now->subDay()->format('Y-m-d'),
            'end_time' => $now->subDay()->format('h:i'),
        ]);

        $response = $this->getJson('api/places/search/similar');
        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                ['lastIndexPresence' => $moment->day->format('Y-m-d')]],
        ]);
    }

    #[DataProvider('indexCountDataProvider')]
    public function testIndexCountShouldImplementRecentIndexen(
        ?int $dateOfTestModifider,
        ?int $dateOfSymptomsOnSetModifier,
        ?int $createdAtModifier,
        int $indexCount,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        CarbonImmutable::setTestNow(CarbonImmutable::now());
        ConfigHelper::set('misc.context.unique_index_count_recent_days', 30);

        $dateOfTest = $dateOfTestModifider !== null ? CarbonImmutable::now()->subDays($dateOfTestModifider) : null;
        $dateOfSymptomsOnSet = $dateOfSymptomsOnSetModifier !== null ? CarbonImmutable::now()->subDays($dateOfSymptomsOnSetModifier) : null;
        // Default outside the limit as created_at is required but should not be inside the limit if null
        $createdAt = CarbonImmutable::now()->subDays($createdAtModifier ?? 31);

        $case = $this->createCaseForOrganisation($organisation, [
            'date_of_test' => $dateOfTest,
            'date_of_symptom_onset' => $dateOfSymptomsOnSet,
            'created_at' => $createdAt,
        ]);
        $place = $this->createPlaceForOrganisation($organisation);
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        $response = $this->be($user)->getJson('api/places/search/similar');

        $response->assertJson(
            [
                'data' => [
                    [
                        'indexCount' => $indexCount,
                    ],
                ],
            ],
        );
    }

    public static function indexCountDataProvider(): Generator
    {
        yield '(A) Should have => tested within limit | without date of symptom | without created at' => [
            29,
            null,
            null,
            1,
        ];

        yield '(B) Should not have => tested outside limit | without date of symptom | without created at' => [
            31,
            null,
            null,
            0,
        ];

        yield '(C) Should not have => tested within limit | date of symptom outside limit | without created at' => [
            29,
            31,
            null,
            0,
        ];

        yield '(D) Should have => tested outside limit | date of symptom within limit | without created at' => [
            31,
            29,
            null,
            1,
        ];

        yield '(E) Should not have => without tested | without date of symptom | created at outside limit' => [
            null,
            null,
            31,
            0,
        ];

        yield '(F) Should  have => without tested | without date of symptom | created at inside limit' => [
            null,
            null,
            29,
            1,
        ];
    }

    public function testPlaceLastIndexPresenceShouldListNullIfNoMomentIsPresent(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $case = $this->createCaseForUser($user);
        $place = $this->createPlaceForOrganisation($organisation);
        $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        $response = $this->getJson('api/places/search/similar');
        $response->assertStatus(200);

        $response->assertJson(
            [
                'data' => [
                    [
                        'lastIndexPresence' => null,
                    ],
                ],
            ],
        );
    }

    public function testNewPlaceIsLinkedToOrganisationByZipcode(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $this->createZipcode([
            'zipcode' => '1234AB',
            'organisation_uuid' => $organisation->uuid,
        ]);

        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);
        $place = $placeService->createPlace([
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'address' => [
                'street' => 'street',
                'postalCode' => '1234AB',
            ],
        ]);
        $this->assertEquals($place->organisation_uuid, $organisation->uuid);
    }

    public function testUpdatedPlaceIsLinkedToOrganisationByZipcode(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $this->createZipcode([
            'zipcode' => '1234AB',
            'organisation_uuid' => $organisation->uuid,
        ]);

        $otherOrganisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $this->createZipcode([
            'zipcode' => '1111XX',
            'organisation_uuid' => $otherOrganisation->uuid,
        ]);

        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);
        $place = $placeService->createPlace([
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'address' => [
                'street' => 'street',
                'postalCode' => '1111XX',
            ],
        ]);

        $place = $placeService->updatePlace($place, [
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'location_id' => null,
            'address' => [
                'street' => 'street',
                'postalCode' => '1234AB',
                'houseNumber' => null,
                'houseNumberSuffix' => null,
                'town' => null,
            ],
            'ggd' => [
                'code' => null,
                'municipality' => null,
            ],
            'isVerified' => null,
        ]);

        $this->assertEquals($place->organisation_uuid, $organisation->uuid);
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    public function testUpdatePlaceWillUpdateSituationNumbers(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $place = $this->createPlaceForOrganisation($organisation);

        $updateSituation = $this->createSituationForPlace($place);
        $deleteSituation = $this->createSituationForPlace($place);

        $response = $this->putJson("/api/places/{$place->uuid}", [
            'label' => $this->faker->name(),
            'category' => $this->faker->randomElement(ContextCategory::all()),
            'situationNumbers' =>
                [
                    [
                        'name' => $createdName = $this->faker->name(),
                        'value' => $createdValue = $this->faker->uuid(),
                    ],
                    [
                        'uuid' => $updateSituation->uuid,
                        'name' => $updatedName = $this->faker->name(),
                        'value' => $updatedValue = $this->faker->uuid(),
                    ],
                ],
        ]);
        $response->assertOk();

        $createdSituation = EloquentSituation::where('name', $createdName)->first();

        $this->assertDatabaseHas('situation', ['name' => $createdName, 'hpzone_number' => $createdValue]);
        $this->assertDatabaseHas('situation_place', ['situation_uuid' => $createdSituation->uuid, 'place_uuid' => $place->uuid]);

        $this->assertDatabaseHas('situation', ['uuid' => $updateSituation->uuid, 'name' => $updatedName, 'hpzone_number' => $updatedValue]);
        $this->assertDatabaseHas('situation_place', ['situation_uuid' => $updateSituation->uuid, 'place_uuid' => $place->uuid]);

        $this->assertDatabaseMissing('situation', ['uuid' => $deleteSituation->uuid]);
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    public function testUpdatePlaceWillUpdateSituationNumbersIfNameOrValueIsEmpty(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $place = $this->createPlaceForOrganisation($organisation);

        $updateSituationOne = $this->createSituationForPlace($place);
        $updateSituationTwo = $this->createSituationForPlace($place);
        $deleteSituation = $this->createSituationForPlace($place);

        $response = $this->putJson("/api/places/{$place->uuid}", [
            'label' => $this->faker->name(),
            'category' => $this->faker->randomElement(ContextCategory::all()),
            'situationNumbers' =>
                [
                    [
                        'name' => $createdName = $this->faker->name(),
                        'value' => null,
                    ],
                    [
                        'name' => null,
                        'value' => $createdValue = $this->faker->name(),
                    ],
                    [
                        'uuid' => $updateSituationOne->uuid,
                        'name' => $updatedName = $this->faker->name(),
                        'value' => null,
                    ],
                    [
                        'uuid' => $updateSituationTwo->uuid,
                        'name' => null,
                        'value' => $updatedValue = $this->faker->name(),
                    ],
                ],
        ]);
        $response->assertOk();

        $createdSituationOne = EloquentSituation::where('name', $createdName)->first();
        $createdSituationTwo = EloquentSituation::where('hpzone_number', $createdValue)->first();

        $this->assertDatabaseHas('situation', ['name' => $createdName, 'hpzone_number' => '']);
        $this->assertDatabaseHas('situation_place', ['situation_uuid' => $createdSituationOne->uuid, 'place_uuid' => $place->uuid]);

        $this->assertDatabaseHas('situation', ['name' => '', 'hpzone_number' => $createdValue]);
        $this->assertDatabaseHas('situation_place', ['situation_uuid' => $createdSituationTwo->uuid, 'place_uuid' => $place->uuid]);

        $this->assertDatabaseHas('situation', ['uuid' => $updateSituationOne->uuid, 'name' => $updatedName, 'hpzone_number' => '']);
        $this->assertDatabaseHas('situation', ['uuid' => $updateSituationTwo->uuid, 'name' => '', 'hpzone_number' => $updatedValue]);

        $this->assertDatabaseMissing('situation', ['uuid' => $deleteSituation->uuid]);
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    public function testPlaceServiceShouldReturnSituations(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $place = $this->createPlaceForOrganisation($organisation);
        $situation = $this->createSituationForPlace($place);

        $response = $this->getJson('/api/places/search/similar');
        $response->assertJson([
            'data' => [
                [
                    'situationNumbers' => [
                        [
                            'uuid' => $situation->uuid,
                            'name' => $situation->name,
                            'value' => $situation->hpzone_number,
                        ]]]]]);
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    public function testPlaceDeletingSituationShouldNotTouchOtherPlaceSituations(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        $place = $this->createPlaceForOrganisation($organisation);
        $deletedSituation = $this->createSituationForPlace($place);

        $untouchedPlace = $this->createPlaceForOrganisation($organisation);
        $untouchedSituation = $this->createSituationForPlace($untouchedPlace);

        $response = $this->putJson("/api/places/{$place->uuid}", [
            'label' => $this->faker->name(),
            'category' => $this->faker->randomElement(ContextCategory::all()),
            'situationNumbers' => [],
        ]);
        $response->assertOk();

        $this->assertDatabaseMissing('situation', ['uuid' => $deletedSituation->uuid]);

        $this->assertDatabaseHas(
            'situation',
            ['name' => $untouchedSituation->name, 'hpzone_number' => $untouchedSituation->hpzone_number],
        );
        $this->assertDatabaseHas('situation_place', ['situation_uuid' => $untouchedSituation->uuid, 'place_uuid' => $untouchedPlace->uuid]);
    }

    public function testNewPlaceIsLinkedToFallbackToCovidCaseOrganisation(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $case = $this->createCaseForUser($user);
        /** @var Context $context */
        $context = $this->createContextWithNewPlace($case);
        $context->refresh();

        $this->assertEquals($context->place->organisation_uuid, $case->organisation->uuid);
    }

    public function testNewPlaceWithoutZipcodeIsLinkedToFallbackToCovidCaseOrganisation(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $case = $this->createCaseForUser($user);

        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);
        $place = $placeService->createPlace([
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'address' => [
                'street' => 'street',
            ],
        ]);

        $this->createContextWithExistingPlace($case, $place);
        $place->refresh();

        $this->assertEquals($place->organisation_uuid, $case->organisation->uuid);
    }

    public function testNewPlaceIsLinkedToFallbackToUserOrganisation(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        /** @var EloquentUser $user */
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        /** @var AuthenticationService $authService */
        $authService = app(AuthenticationService::class);

        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);
        $place = $placeService->createPlace([
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'address' => [
                'street' => 'street',
                'postalCode' => '1234AB',
            ],
        ]);

        $this->assertEquals($place->organisation_uuid, $authService->getSelectedOrganisation()->uuid);
    }

    public function testCreatePlaceNoOrganisationFallbackFound(): void
    {
        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);
        $place = $placeService->createPlace([
            'label' => 'label',
            'category' => ContextCategory::verenigingOverige(),
            'address' => [
                'street' => 'street',
                'postalCode' => '1234AB',
            ],
        ]);

        $this->assertEquals($place->organisation_uuid, null);
    }
}
