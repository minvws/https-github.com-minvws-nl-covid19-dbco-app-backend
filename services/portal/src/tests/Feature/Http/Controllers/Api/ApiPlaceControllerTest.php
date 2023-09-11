<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\UpdatePlaceCounters;
use App\Models\Context\Circumstances;
use App\Models\Context\Contact;
use App\Models\CovidCase\Deceased;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Symptoms;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentSituation;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use App\Models\OrganisationType;
use App\Models\Versions\CovidCase\Deceased\DeceasedV1;
use App\Repositories\DbPlaceRepository;
use App\Schema\Types\SchemaType;
use App\Services\Location\LocationClient;
use App\Services\Place\MergeService;
use App\Services\PlaceService;
use Carbon\CarbonImmutable;
use Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function collect;
use function json_encode;
use function sprintf;

#[Group('place')]
#[Group('guzzle')]
#[Group('validation')]
class ApiPlaceControllerTest extends FeatureTestCase
{
    public function testCreateMinimalPlaceShouldBeSuccessful(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $response = $this->postJson('api/places', [
            'label' => 'Some place',
            'category' => ContextCategory::accomodatieBinnenland()->value,
        ]);
        $response->assertStatus(201);
        $place = $response->json();
        $this->assertNotNull($place['uuid']);
        $this->assertEquals(true, $place['editable']);
        $this->assertEquals(0, $place['indexCount']);
        $this->assertEquals('Some place', $place['label']);
    }

    public function testCreatePlaceWithIdStoresLocationId(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $response = $this->postJson('api/places', [
            'label' => 'Some place',
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'id' => 'foo',
        ]);
        $response->assertStatus(201);

        $place = $response->json();

        $this->assertDatabaseHas('place', [
            'uuid' => $place['uuid'],
            'location_id' => 'foo',
        ]);
    }

    public function testCreatePlaceWithIncorrectPostalCode(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $response = $this->postJson('api/places', [
            'label' => 'Some place',
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'id' => 'foo',
            'address' => [
                'street' => 'Testerweg',
                'houseNumber' => '23',
                'houseNumberSuffix' => null,
                'postalCode' => '5678 MN',
                'town' => 'Duckstad',
                'country' => 'NL',
            ],
        ]);
        $response->assertStatus(201);

        $place = $response->json();
        $this->assertDatabaseHas('place', [
            'uuid' => $place['uuid'],
            'postalCode' => '5678MN',
        ]);
    }

    #[DataProvider('placesNoResultsDataProvider')]
    public function testGetPlacesNoResults(?string $postalCode): void
    {
        $this->mockLocationApiEmptyResponse();

        $user = $this->createUser();
        $this->be($user);

        $response = $this->getJson(sprintf('api/places/search?query=%s', $postalCode));

        $response->assertStatus(200);
        $response->assertJson([
            'places' => [],
            'suggestions' => [],
        ]);
    }

    public static function placesNoResultsDataProvider(): array
    {
        return [
            'valid postalcode' => ['1234AB'],
            'invalid postalcode' => ['foo'],
            'no postalcode' => [null],
        ];
    }

    public function testGetPlacesOnlyPlaces(): void
    {
        $this->mockLocationApiEmptyResponse();

        $user = $this->createUser();
        $this->be($user);

        $place = $this->createPlaceWithContextCategory(ContextCategory::overig());

        $response = $this->getJson('api/places/search?query=1234AA');
        $response->assertStatus(200);

        $response->assertJson(
            [
                'places' => [
                    [
                        'uuid' => $place->uuid,
                        'label' => 'Place',
                        'indexCount' => null,
                        'indexCountSinceReset' => null,
                        'indexCountResetAt' => null,
                        'category' => ContextCategory::overig()->value,
                        'addressLabel' => 'Testerweg 23, 1234AA Duckstad',
                        'address' => [
                            'street' => 'Testerweg',
                            'houseNumber' => '23',
                            'houseNumberSuffix' => null,
                            'postalCode' => '1234AA',
                            'town' => 'Duckstad',
                            'country' => 'NL',
                        ],
                        'isVerified' => false,
                        'source' => 'manual',
                        'ggd' => [
                            'code' => 'GG2511',
                            'municipality' => 'Amersfoort',
                        ],
                    ],
                ],
                'suggestions' => [],
            ],
            true,
        );
    }

    #[DataProvider('doAddressLookUpPermissionChecks')]
    public function testAddressLookUpPermissionChecks(string $role, bool $expectedAccess): void
    {
        $this->mockLocationApiResponse();

        $user = $this->createUser([], $role);
        $this->be($user);

        $response = $this->getJson('api/addresses?postalCode=1234AA&houseNumber=101');
        $response->assertStatus($expectedAccess ? 200 : 403);
    }

    public static function doAddressLookUpPermissionChecks(): iterable
    {
        return [
            'role "user" has permission' => ['user', true],
            'role "planner" has permission' => ['planner', true],
            'role "compliance" has no permission' => ['compliance', false],
        ];
    }

    public function testGetPlacesOnlySuggestions(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->mockLocationApiResponse();

        $response = $this->getJson('api/places/search?query=1234AA');
        $response->assertStatus(200);
        $response->assertJson(
            [
                'places' => [],
                'suggestions' => [
                    [
                        'id' => 'b7f5e8308ccab144a10f7e46b55c2791',
                        'label' => 'De Kluis',
                        'indexCount' => 0,
                        'category' => null,
                        'addressLabel' => 'Euroweg 23, 1234AA Duckstad',
                        'address' => [
                            'street' => 'Euroweg',
                            'houseNumber' => '23',
                            'houseNumberSuffix' => null,
                            'postalCode' => '1234AA',
                            'town' => 'Duckstad',
                        ],
                        'ggd' => [
                            'code' => 'GG2511',
                            'municipality' => 'Amersfoort',
                        ],
                    ],
                ],
            ],
            true,
        );
    }

    public function testGetPlacesBothDataSets(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->mockLocationApiResponse();

        $place = $this->createPlaceWithContextCategory(ContextCategory::overig());

        $response = $this->getJson('api/places/search?query=1234AA');
        $response->assertStatus(200);

        $response->assertJson(
            [
                'places' => [
                    [
                        'uuid' => $place->uuid,
                        'label' => 'Place',
                        'indexCount' => null,
                        'category' => ContextCategory::overig()->value,
                        'addressLabel' => 'Testerweg 23, 1234AA Duckstad',
                        'address' => [
                            'street' => 'Testerweg',
                            'houseNumber' => '23',
                            'houseNumberSuffix' => null,
                            'postalCode' => '1234AA',
                            'town' => 'Duckstad',
                            'country' => 'NL',
                        ],
                        'isVerified' => false,
                        'source' => 'manual',
                        'ggd' => [
                            'code' => 'GG2511',
                            'municipality' => 'Amersfoort',
                        ],
                    ],
                ],
                'suggestions' => [
                    [
                        'id' => 'b7f5e8308ccab144a10f7e46b55c2791',
                        'label' => 'De Kluis',
                        'indexCount' => 0,
                        'category' => null,
                        'addressLabel' => 'Euroweg 23, 1234AA Duckstad',
                        'address' => [
                            'street' => 'Euroweg',
                            'houseNumber' => '23',
                            'houseNumberSuffix' => null,
                            'postalCode' => '1234AA',
                            'town' => 'Duckstad',
                        ],
                        'ggd' => [
                            'code' => 'GG2511',
                            'municipality' => 'Amersfoort',
                        ],
                    ],
                ],
            ],
            true,
        );
    }

    public function testGetExistingPlacesOverLocations(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->mockLocationApiResponse();

        $place = $this->createPlaceWithContextCategory(ContextCategory::overig());
        $place->location_id = 'b7f5e8308ccab144a10f7e46b55c2791';
        $place->save();

        $response = $this->getJson('api/places/search?query=1234AA');
        $response->assertStatus(200);

        $response->assertJson(
            [
                'places' => [
                    [
                        'uuid' => $place->uuid,
                        'label' => 'Place',
                        'indexCount' => null,
                        'category' => ContextCategory::overig()->value,
                        'addressLabel' => 'Testerweg 23, 1234AA Duckstad',
                        'address' => [
                            'street' => 'Testerweg',
                            'houseNumber' => '23',
                            'houseNumberSuffix' => null,
                            'postalCode' => '1234AA',
                            'town' => 'Duckstad',
                            'country' => 'NL',
                        ],
                        'isVerified' => false,
                        'source' => 'external',
                        'ggd' => [
                            'code' => 'GG2511',
                            'municipality' => 'Amersfoort',
                        ],
                    ],
                ],
                'suggestions' => [],
            ],
            true,
        );
    }

    public function testPlaceCreate(): void
    {
        $user = $this->createUser();

        $placePayload = [
            'label' => 'hello',
            'address' => [
                'postalCode' => '6666 FF',
                'street' => 'street',
                'houseNumber' => '5',
                'houseNumberSuffix' => null,
                'town' => 'place',
                'country' => 'NL',
            ],
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'locationId' => null,
        ];

        $createPlaceResponse = $this->be($user)->postJson('/api/places', $placePayload);
        $createPlaceResponse->assertStatus(201);

        $createPlaceResponse->assertJson([
            'label' => 'hello',
            'indexCount' => 0,
            'category' => 'accomodatie_binnenland',
            'addressLabel' => 'street 5, 6666FF place',
            'address' => [
                'postalCode' => '6666FF',
                'street' => 'street',
                'houseNumber' => '5',
                'houseNumberSuffix' => null,
                'town' => 'place',
            ],
            'ggd' => [
                'code' => null,
                'municipality' => null,
            ],
            'isVerified' => false,
            'editable' => true,
        ], false);
    }

    public function testPostalCodeIsNormalizedWhenPlaceIsCreated(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $placePayload = [
            'label' => 'hello',
            'address' => [
                'postalCode' => '6666 FF',
                'street' => 'street',
                'houseNumber' => '5',
                'houseNumberSuffix' => null,
                'town' => 'place',
                'country' => 'NL',
            ],
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'locationId' => null,
        ];

        $createPlaceResponse = $this->postJson('/api/places', $placePayload);
        $createPlaceResponse->assertStatus(201);

        $this->assertDatabaseHas('place', [
            'uuid' => $createPlaceResponse->json()['uuid'],
            'postalcode' => '6666FF',
            'is_verified' => false,
        ]);
    }

    public function testPostalCodeIsNormalizedWhenPlaceIsUpdated(): void
    {
        $organisation = $this->createOrganisation(['type' => OrganisationType::regionalGGD()]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $response = $this->be($user)->putJson(sprintf('api/places/%s', $place->uuid), [
            'label' => 'label',
            'location_id' => 'location_id',
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'address' => [
                'street' => 'street',
                'postalCode' => '7777 FF',
                'houseNumber' => 'houseNumer',
                'houseNumberSuffix' => 'houseNumberSuffix',
                'town' => 'town',
            ],
            'ggd' => [
                'code' => 'code',
                'municipality' => 'municipality',
            ],
            'isVerified' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('place', [
            'uuid' => $place->uuid,
            'postalcode' => '7777FF',
        ]);
    }

    public function testLinkPlaceToContext(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $place = $this->createPlace();

        $response = $this->be($user)->postJson(sprintf('api/contexts/%s/place/%s', $context->uuid, $place->uuid));
        $response->assertStatus(201);
        $this->assertDatabaseHas('place', [
            'uuid' => $place->uuid,
            'organisation_uuid' => $case->organisation_uuid,
        ]);
    }

    public function testLinkPlaceWithoutPostalcodeToContextWhenCaseIsOutsourced(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);

        $case = $this->createCaseForOrganisation($organisation);

        $context = $this->createContextForCase($case);
        $case->save();

        /** @var PlaceService $placeService */
        $placeService = app(PlaceService::class);

        $outsourceUser = $this->createUserForOrganisation($outsourceOrganisation);
        $this->be($outsourceUser);

        // Create place without postalcode
        $place = $placeService->createPlace(
            [
                'label' => 'abc',
                'category' => ContextCategory::bouw()->value,
            ],
        );

        //Assign case
        $case->assigned_user_uuid = $outsourceUser->uuid;
        $case->assigned_organisation_uuid = $outsourceOrganisation->uuid;
        $case->save();

        $response = $this->postJson(sprintf('api/contexts/%s/place/%s', $context->uuid, $place->uuid));
        $response->assertStatus(201);
        $this->assertDatabaseHas('place', [
            'uuid' => $place->uuid,
            'organisation_uuid' => $outsourceOrganisation->uuid,
        ]);
    }

    #[DataProvider('updatePlaceValidationInvalidData')]
    public function testUpdatePlaceValidation(array $postData): void
    {
        $organisation = $this->createOrganisation(['type' => OrganisationType::regionalGGD()]);
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $response = $this->be($user)->putJson(sprintf('api/places/%s', $place->uuid), $postData);

        $response->assertStatus(422);
    }

    public static function updatePlaceValidationInvalidData(): array
    {
        $validPostData = [
            'label' => 'label',
            'location_id' => 'location_id',
            'category' => ContextCategory::accomodatieBinnenland()->value,
            'address' => [
                'street' => 'street',
                'postalCode' => '1234AB',
                'houseNumber' => 'houseNumer',
                'houseNumberSuffix' => 'houseNumberSuffix',
                'town' => 'town',
            ],
            'ggd' => [
                'code' => 'code',
                'municipality' => 'municipality',
            ],
            'isVerified' => true,
        ];

        return [
            'without label' => [collect($validPostData)->forget('label')->all()],
            'invalid category' => [collect($validPostData)->put('category', 'foo')->all()],
        ];
    }

    private function createPlaceWithContextCategory(ContextCategory $category): Place
    {
        return $this->createPlace([
            'label' => 'Place',
            'street' => 'Testerweg',
            'housenumber' => '23',
            'postalcode' => '1234AA',
            'town' => 'Duckstad',
            'category' => $category,
            'ggd_code' => 'GG2511',
            'ggd_municipality' => 'Amersfoort',
            'is_verified' => false,
        ]);
    }

    private function mockLocationApiEmptyResponse(): void
    {
        $mockHandler = new MockHandler();

        $this->app->instance(LocationClient::class, new LocationClient([
            'handler' => HandlerStack::create($mockHandler),
        ]));

        $mockHandler->append(new GuzzleResponse(Response::HTTP_OK, [], json_encode([
            'locations' => [],
        ])));
    }

    private function mockLocationApiResponse(): void
    {
        $mockHandler = new MockHandler();

        $this->app->instance(
            LocationClient::class,
            new LocationClient(['handler' => HandlerStack::create($mockHandler)]),
        );

        $mockHandler->append(
            new GuzzleResponse(
                Response::HTTP_OK,
                [],
                json_encode(
                    [
                        'locations' => [
                            [
                                'bag_id' => 123_124_121,
                                'business' => false,
                                'city' => 'Duckstad',
                                'contact' => [
                                    'email' => 'some@example.com',
                                    'kvk' => null,
                                    'tel' => '088 5555555',
                                    'url' => 'https://example.com/',
                                ],
                                'geo' => [
                                    'lat' => 52.29_179,
                                    'lon' => 5.9_074_568,
                                ],
                                'ggd' => [
                                    'city' => null,
                                    'code' => 'GG2511',
                                    'municipality' => 'Amersfoort',
                                    'name' => 'GGD Regio Utrecht',
                                ],
                                'house_number' => '23',
                                'house_number_extension' => null,
                                'id' => 'b7f5e8308ccab144a10f7e46b55c2791',
                                'location_name' => 'De Kluis',
                                'meta' => [
                                    'google_places_id' => 'ChIJ71Bj1PxGxkcRNu_IaPbn_hI',
                                ],
                                'sources' => [
                                    'google_places',
                                ],
                                'street_name' => 'Euroweg',
                                'country' => null,
                                'type' => 'adres',
                                'zipcode' => '1234AA',
                            ],
                        ],
                    ],
                ),
            ),
        );
    }

    public function testMergeLocations(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        // create main place
        $mainPlace = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        // give it some Contexts of which some have no label (which is possible)
        $this->createContextForPlace($mainPlace);
        $this->createContextForPlace($mainPlace);
        $this->createContextForPlace($mainPlace);
        $this->createContextForPlace($mainPlace, ['label' => null]);
        $this->createContextForPlace($mainPlace, ['label' => null]);

        // create places to merge
        $place1 = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
        ]);
        $place2 = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
        ]);
        $place3 = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
        ]);
        $place4 = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
        ]);
        $place5 = $this->createPlaceForOrganisation($organisation, [
            'label' => 'foo',
            'is_verified' => true,
        ]);

        $section1 = $this->createSectionForPlace($place1);
        $section2 = $this->createSectionForPlace($place2);

        $context1 = $this->createContextForPlace($place3);
        $context2 = $this->createContextForPlace($place4);

        $case = $this->createCaseForOrganisation($organisation);

        /** @var EloquentSituation $situation */
        $situation = EloquentSituation::factory()->create();
        $situation->cases()->attach($case);
        $situation->places()->attach($place5);

        $mergePlaces = collect([
            $place1,
            $place2,
            $place3,
            $place4,
            $place5,
        ]);

        $response = $this->be($user)->put("/api/places/{$mainPlace->uuid}/merge", [
            'merge_places' => $mergePlaces->pluck('uuid')->toArray(),
        ]);

        $response->assertStatus(200)->assertJson($mainPlace->refresh()->toArray());

        // assert places are merged
        $this->assertDatabaseCount(Place::class, 1);
        $this->assertDatabaseMissing(Place::class, collect($mergePlaces)->mapWithKeys(static function (string $uuid) {
            return ['uuid' => $uuid];
        })->toArray());

        // assert contexts are now pointing to the main place
        $this->assertDatabaseHas(Context::class, [
            'uuid' => $context1->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
        $this->assertDatabaseHas(Context::class, [
            'uuid' => $context2->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);

        // assert sections are now pointing to the main place
        $this->assertDatabaseHas(Section::class, [
            'uuid' => $section1->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
        $this->assertDatabaseHas(Section::class, [
            'uuid' => $section2->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);

        // assert case is now pointing to the main place (through the same situation)
        $this->assertDatabaseHas('situation_case', [
            'situation_uuid' => $situation->uuid,
            'case_uuid' => $case->uuid,
        ]);
        $this->assertDatabaseHas('situation_place', [
            'situation_uuid' => $situation->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
    }

    public function testKeepUniqueSituationsWhenMergingLocations(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        // Create main place
        $mainPlace = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        // Create place to merge into main place
        $place = $this->createPlaceForOrganisation($organisation, ['is_verified' => true]);

        // Create 4 places, 2 of which have the same name
        /** @var EloquentSituation $situation1 */
        $situation1 = EloquentSituation::factory()->create(['hpzone_number' => '22222']);
        /** @var EloquentSituation $situation2 */
        $situation2 = EloquentSituation::factory()->create(['hpzone_number' => '33333']);
        /** @var EloquentSituation $situation3 */
        $situation3 = EloquentSituation::factory()->create(['hpzone_number' => '33333']);
        /** @var EloquentSituation $situation4 */
        $situation4 = EloquentSituation::factory()->create(['hpzone_number' => '44444']);

        // Attach the place to each of the situations. Situation with the same name are attached to different places.
        $situation1->places()->attach($mainPlace);
        $situation2->places()->attach($mainPlace);
        $situation3->places()->attach($place);
        $situation4->places()->attach($place);

        // Merge the place into the main place
        $response = $this->be($user)->put("/api/places/{$mainPlace->uuid}/merge", [
            'merge_places' => [$place->uuid],
        ]);

        // Assert that the merge was successful
        $response->assertStatus(200)->assertJson($mainPlace->refresh()->toArray());

        // Assert that the main place has 3 situations, not 4, because the situation with the duplicate hpzone_number (33333)
        // should not be copied over to the main place.
        self::assertCount(3, $mainPlace->situations);

        // Assert that the main place is still attached to situation 22222 and 33333
        $this->assertDatabaseHas('situation_place', [
            'situation_uuid' => $situation1->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
        $this->assertDatabaseHas('situation_place', [
            'situation_uuid' => $situation2->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
        // Assert that the main place is not attached to the other situation with hpzone_number 33333
        $this->assertDatabaseMissing('situation_place', [
            'situation_uuid' => $situation3->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
        // Assert that there is no relation between the merged place and the other situation with hpzone_number 33333
        $this->assertDatabaseMissing('situation_place', [
            'situation_uuid' => $situation3->uuid,
            'place_uuid' => $place->uuid,
        ]);
        // Assert that the main place is attached to situation 44444 that was attached to the other place before the
        // merge.
        $this->assertDatabaseHas('situation_place', [
            'situation_uuid' => $situation4->uuid,
            'place_uuid' => $mainPlace->uuid,
        ]);
    }

    #[DataProvider('mergeLocationsWithIndexCountResetProvider')]
    public function testMergeLocationsWithIndexCountReset(
        ?int $mainPlaceIndexCountResetAtModifier,
        ?int $otherPlaceIndexCountResetAtModifier,
        ?int $expectedIndexCountResetAtModifier,
    ): void {
        $mainPlaceIndexCountResetAt = $mainPlaceIndexCountResetAtModifier !== null
            ? CarbonImmutable::now()->subDays($mainPlaceIndexCountResetAtModifier)
            : null;
        $otherPlaceIndexCountResetAt = $otherPlaceIndexCountResetAtModifier !== null
            ? CarbonImmutable::now()->subDays($otherPlaceIndexCountResetAtModifier)->roundSeconds()
            : null;
        $expectedIndexCountResetAt = $expectedIndexCountResetAtModifier !== null
            ? CarbonImmutable::now()->subDays($expectedIndexCountResetAtModifier)->roundSeconds()
            : null;

        // Given a user with role context manager belonging to an organisation
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        // And a Place of which the index count has been reset at a certain date
        $mainPlace = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
            'index_count_reset_at' => $mainPlaceIndexCountResetAt,
        ]);

        // And another Place of which the index count has been reset at a certain date
        $otherPlace = $this->createPlaceForOrganisation($organisation, [
            'is_verified' => true,
            'index_count_reset_at' => $otherPlaceIndexCountResetAt,
        ]);

        // When the other Place is merged into the Place
        $response = $this->be($user)->put("/api/places/{$mainPlace->uuid}/merge", [
            'merge_places' => [$otherPlace->uuid],
        ]);

        // Then the returned result of the merge is the main Place
        $mainPlaceJson = $mainPlace->refresh()->toArray();
        $response->assertStatus(200)->assertJson($mainPlaceJson);

        // And there is only one Place left in the database after the merge
        self::assertDatabaseCount(Place::class, 1);

        // And the index count of the resulting Place is set to the oldest (or null) date of all merged Places
        self::assertEquals($expectedIndexCountResetAt?->toIsoString(), $response->json('index_count_reset_at'));
    }

    public static function mergeLocationsWithIndexCountResetProvider(): Generator
    {
        yield 'The oldest check date is used' => [0, 1, 1];
        yield 'One context has never been checked' => [null, 0, null];
    }

    public function testMergeLocationsWillDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        // Given a user with role context manager belonging to an organisation
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');

        // And a Place of which the index count has been reset at a certain date
        $mainPlace = $this->createPlaceForOrganisation($organisation);

        // And another Place of which the index count has been reset at a certain date
        $otherPlace = $this->createPlaceForOrganisation($organisation);

        // When the other Place is merged into the Place
        $this->be($user)->put("/api/places/{$mainPlace->uuid}/merge", [
            'merge_places' => [$otherPlace->uuid],
        ]);

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }

    #[DataProvider('createSectionsDataProvider')]
    public function testCreateSections(
        bool $useContextUuid,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        // Create usable place
        $place = $this->createPlace([
            'organisation_uuid' => $organisation->uuid,
        ]);

        // Create usable context
        $context = $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
        ]);

        // Create 3 existing sections & link them to the context
        $section1 = $this->createSectionForPlace($place);
        $context->sections()->save($section1);

        $section2 = $this->createSectionForPlace($place);
        $context->sections()->save($section2);

        $section3 = $this->createSectionForPlace($place);
        $context->sections()->save($section3);

        // Create a new record payload
        $payloadSection1 = [
            'label' => 'new record',
        ];

        // Update an existing record payload
        $payloadSection2 = [
            'uuid' => $section1->uuid,
            'label' => 'new label',
        ];

        // Format the payload
        $payload = [
            'context_uuid' => $useContextUuid ? $context->uuid : null,
            'sections' => [
                $payloadSection1,
                $payloadSection2,
            ],
        ];

        // Make the request with payload
        $response = $this->putJson("/api/places/{$place->uuid}/sections", $payload);

        // Assert that status & json and make sure only one section is returned
        $response->assertStatus(200)->assertJsonCount(1, 'sections');

        // should be one new section
        $this->assertDatabaseCount('section', 4);

        // Assert that the new record can be found within the database
        $this->assertDatabaseHas('section', $payloadSection1);

        // Assert that the record has been updated
        $this->assertDatabaseMissing('section', $payloadSection2);

        // Assert that the section is linked if context uuid was given
        if ($useContextUuid) {
            $this->assertDatabaseHas('context_section', [
                'context_uuid' => $context->uuid,
                'section_uuid' => $response->json('sections')[0]['uuid'],
            ]);
        } else {
            $this->assertDatabaseMissing('context_section', [
                'section_uuid' => $response->json('sections')[0]['uuid'],
            ]);
        }
    }

    public static function createSectionsDataProvider(): Generator
    {
        yield 'Context UUID given' => [
            'useContextUuid' => true,
        ];

        yield 'Context UUID not given' => [
            'useContextUuid' => false,
        ];
    }

    #[DataProvider('updateSectionsDataProvider')]
    public function testUpdateSections(
        bool $useContextUuid,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        // Create usable place
        $place = $this->createPlaceForOrganisation($organisation);

        // Create usable context
        $context = $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
        ]);

        // Create 3 existing sections & link them to the context
        $section1 = $this->createSectionForPlace($place);
        $context->sections()->save($section1);

        $section2 = $this->createSectionForPlace($place);
        $context->sections()->save($section2);

        $section3 = $this->createSectionForPlace($place);
        $context->sections()->save($section3);

        // Create a new record payload
        $payloadSection1 = [
            'label' => 'new record',
        ];

        // Update an existing record payload
        $payloadSection2 = [
            'uuid' => $section1->uuid,
            'label' => 'new label',
        ];

        // Format the payload
        $payload = [
            'context_uuid' => $useContextUuid ? $context->uuid : null,
            'sections' => [
                $payloadSection1,
                $payloadSection2,
            ],
        ];

        // Make the request with payload
        $response = $this->patchJson("/api/places/{$place->uuid}/sections", $payload);

        // Assert that status & json and make sure only one section is returned
        $response->assertStatus(200)->assertJsonCount(1, 'sections');

        // should not have a new section
        $this->assertDatabaseCount('section', 3);

        // Assert that the new record can be found within the database
        $this->assertDatabaseMissing('section', $payloadSection1);

        // Assert that the record has been updated
        $this->assertDatabaseHas('section', $payloadSection2);

        // Make sure the existed section is still linked with the context
        $this->assertDatabaseHas('context_section', [
            'context_uuid' => $context->uuid,
            'section_uuid' => $response->json('sections')[1]['uuid'],
        ]);

        // Assert that the section is linked if context uuid was given
        if (!$useContextUuid) {
            return;
        }

        $this->assertDatabaseHas('context_section', [
            'context_uuid' => $context->uuid,
            'section_uuid' => $response->json('sections')[1]['uuid'],
        ]);
    }

    public static function updateSectionsDataProvider(): Generator
    {
        yield 'Context UUID given' => [
            'useContextUuid' => true,
        ];

        yield 'Context UUID not given' => [
            'useContextUuid' => false,
        ];
    }

    public function testMergePlaceSections(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        // Create a main place where all the magic happens
        $mainPlace = $this->createPlace([
            'organisation_uuid' => $organisation->uuid,
        ]);

        // Create a section to be merged towards
        $mainSection = $this->createSectionForPlace($mainPlace);

        // Set default context attributes
        $contextAttributes = ['schema_version' => Context::getSchema()->getCurrentVersion()->getVersion()];

        // Create 3 different contexts
        $context1 = $this->createContextForPlace($mainPlace, $contextAttributes);
        $context2 = $this->createContextForPlace($mainPlace, $contextAttributes);
        $context3 = $this->createContextForPlace($mainPlace, $contextAttributes);
        $context4 = $this->createContextForPlace($mainPlace, $contextAttributes);

        // Create 2 different merge sections
        $mergeSections1 = $this->createSectionForPlace($mainPlace);
        $mergeSections2 = $this->createSectionForPlace($mainPlace);
        $notMergedSection = $this->createSectionForPlace($mainPlace);

        // Save the contexts to the different sections
        $mainSection->contexts()->save($context1);
        $mergeSections1->contexts()->save($context1);
        $mergeSections1->contexts()->save($context2);
        $mergeSections2->contexts()->save($context2);
        $mergeSections2->contexts()->save($context3);
        $mergeSections2->contexts()->save($context4);
        $notMergedSection->contexts()->save($context4);

        // Try to call the method to merge all the merge sections towards the main section
        $response = $this->postJson("/api/places/{$mainPlace->uuid}/sections/{$mainSection->uuid}/merge", [
            'merge_sections' => [
                $mergeSections1->uuid,
                $mergeSections2->uuid,
            ],
        ]);

        // Assert that the response is correct with the correct data
        $response->assertStatus(200)->assertJson([
            'section' => [
                'indexCount' => 4,
            ],
        ], false);

        // Assert that the merged sections are deleted
        $this->assertDatabaseMissing('section', [
            [['uuid' => $mergeSections1->uuid]],
            [['uuid' => $mergeSections2->uuid]],
        ]);

        // Make sure that the contexts are not linked towards the old sections
        $this->assertDatabaseMissing('context_section', [
            [['section_uuid' => $mergeSections1->uuid]],
            [['section_uuid' => $mergeSections2->uuid]],
        ]);

        // Make sure context4 still has a link with the not merged context
        $this->assertDatabaseHas('context_section', [
            'section_uuid' => $notMergedSection->uuid,
            'context_uuid' => $context4->uuid,
        ]);

        // Make sure the contexts are linked towards the main section
        $this->assertEquals(
            3,
            DB::table('context_section')
                ->where('section_uuid', $mainSection->uuid)
                ->whereIn('context_uuid', [
                    $context1->uuid,
                    $context2->uuid,
                    $context3->uuid,
                ])
                ->count(),
        );

        // Assert that the index count from the main section is 4 (all contexts)
        $this->assertEquals(4, $mainSection->indexCount());
    }

    public function testMergePlaceSectionReturnsBadRequestWhenSectionDoesNotBelongToPlace(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlaceForOrganisation($organisation);
        $placeBelongingToSection = $this->createPlaceForOrganisation($organisation);
        $section = $this->createSectionForPlace($placeBelongingToSection);
        $sectionToMerge = $this->createSectionForPlace($placeBelongingToSection);

        $response = $this->postJson(
            "/api/places/{$place->uuid}/sections/{$section->uuid}/merge",
            [
                'merge_sections' => [
                    $sectionToMerge->uuid,
                ],
            ],
        );

        $response->assertBadRequest();
    }

    public function testContextLabelDuplications(): void
    {
        $organisation = $this->createOrganisation();

        $mainPlace = $this->createPlace([
            'label' => 'place',
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $place1 = $this->createPlace([
            'label' => 'place1',
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);
        $place2 = $this->createPlace([
            'label' => 'place2',
            'is_verified' => true,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $this->createContextForPlace($mainPlace, [
            'label' => 'same',
        ]);
        $context1 = $this->createContextForPlace($place1, [
            'label' => 'same',
        ]);
        $context2 = $this->createContextForPlace($place2, [
            'label' => 'same',
        ]);
        $context3 = $this->createContextForPlace($place2, [
            'label' => 'other',
        ]);

        /** @var MergeService $mergeService */
        $mergeService = app(MergeService::class);
        $mergeService->handle($mainPlace, [
            $place1->uuid,
            $place2->uuid,
        ]);

        $this->assertDatabaseHas(Context::class, [
            'uuid' => $context1->uuid,
            'place_uuid' => $mainPlace->uuid,
            'label' => 'same (place1)',
        ]);
        $this->assertDatabaseHas(Context::class, [
            'uuid' => $context2->uuid,
            'place_uuid' => $mainPlace->uuid,
            'label' => 'same (place2)',
        ]);
        $this->assertDatabaseHas(Context::class, [
            'uuid' => $context3->uuid,
            'place_uuid' => $mainPlace->uuid,
            'label' => 'other',
        ]);
    }

    public function testGetPlaceCasesWhenCaseHasManyContexts(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForOrganisation($organisation, [
            // Should ve set to null, else the factory will fill it and the database trigger will change fields incorrectly
            'date_of_test' => null,
            'date_of_symptom_onset' => null,
            // Should be carbon now, if done with faker the application will not find the record
            'created_at' => CarbonImmutable::now(),
        ]);
        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();

        $this->createContextForPlace($place, [
            'schema_version' => $contextSchemaVersion,
            'covidcase_uuid' => $case->uuid,
            'relationship' => ContextRelationship::visitor(),
        ]);
        $this->createContextForPlace($place, [
            'schema_version' => $contextSchemaVersion,
            'covidcase_uuid' => $case->uuid,
            'relationship' => ContextRelationship::staff(),
        ]);
        $this->createContextForPlace($place, [
            'schema_version' => $contextSchemaVersion,
            'covidcase_uuid' => $case->uuid,
            'relationship' => null,
        ]);

        $response = $this->getJson("/api/places/{$place->uuid}/cases/");

        self::assertEquals(3, $response->json('total'), 'Should return three records for a single case with three contexts');
        self::assertEqualsCanonicalizing(
            [ContextRelationship::visitor()->value, ContextRelationship::staff()->value, '-'],
            collect($response->json('data'))->pluck('relationContext')->toArray(),
        );
    }

    public function testGetPlaceCases(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place1 = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $place2 = $this->createPlace(['organisation_uuid' => $organisation->uuid]);

        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();

        $case1 = $this->seedGetPlaceCaseData($organisation, $place1, $contextSchemaVersion);
        $case2 = $this->seedGetPlaceCaseData($organisation, $place1, $contextSchemaVersion);

        // Add 2 more cases to make sure these don't show within the results
        $this->seedGetPlaceCaseData($organisation, $place2, $contextSchemaVersion);
        $this->seedGetPlaceCaseData($organisation, $place2, $contextSchemaVersion);

        $response = $this->getJson("/api/places/{$place1->uuid}/cases/?perPage=20&page=1&sort=covidcase.created_at&order=desc");

        self::assertEquals(2, $response->json('total'), 'Should only return cases associated to given place');
        self::assertEqualsCanonicalizing([$case1->caseId, $case2->caseId], collect($response->json('data'))->pluck('caseId')->toArray());
    }

    public function testGetPlaceCasesOnlyGetCasesBeforeAGivenGap(): void
    {
        // GIVEN we are a clusterspecialist for an organisation
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        // GIVEN there is a place for the organisation
        $place = $this->createPlaceForOrganisation($organisation);

        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();

        // GIVEN there is a context with case for the place with the current time
        $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion);

        // GIVEN there is a context with case for the place with the current time
        $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, 1);

        // GIVEN there is a context with case for the place with a time that is the gap parameter + 1 day earlier
        $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT + 2);

        // GIVEN there is a context with case for the place with a time that is the gap parameter + 2 days earlier (Within the gap for the second one)
        $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT + 2);

        // DO get the cases from the response
        $response = $this->getJson("/api/places/{$place->uuid}/cases");

        // ASSERT that only 2 record is returned
        $this->assertEquals(2, $response->json('total'), 'Should only return case that is before the gap');
    }

    public function testGetPlaceCasesWillAddNameIfConsented(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion(1, static function (Index $index) use ($firstName, $lastName): void {
                $index->firstname = $firstName;
                $index->lastname = $lastName;
            }),
            'date_of_symptom_onset' => CarbonImmutable::now(), // force database-trigger for episode_start_date to this date
        ]);

        $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
            'covidcase_uuid' => $case->uuid,
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                $contact->notificationNamedConsent = true;
            }),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/places/%s/cases', $place->uuid));

        self::assertEquals(true, $response->json('data.0.notificationNamedConsent'));
        self::assertEquals($firstName, $response->json('data.0.firstName'));
        self::assertEquals($lastName, $response->json('data.0.lastName'));
    }

    public function testGetPlaceCasesWillNotAddNameIfNotConsented(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlaceForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);

        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();
        $context = $this->createContextForPlace($place, ['schema_version' => $contextSchemaVersion, 'covidcase_uuid' => $case->uuid]);

        $context->contact->notificationNamedConsent = false;
        $context->save();

        $response = $this->getJson("/api/places/{$place->uuid}/cases");

        self::assertEquals(false, $response->json('data.0.notificationNamedConsent'));
        self::assertEquals(null, $response->json('data.0.firstName'));
        self::assertEquals(null, $response->json('data.0.lastName'));
    }

    public function testGetPlaceCasesIncludesIsDeceasedStatus(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $case = $this->createCaseForOrganisation($organisation, [
            'date_of_symptom_onset' => CarbonImmutable::now(), // force database-trigger for episode_start_date to this date
            'deceased' => Deceased::newInstanceWithVersion(1, static function (DeceasedV1 $deceasedV1): void {
                $deceasedV1->isDeceased = YesNoUnknown::yes();
            }),
        ]);

        $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
            'covidcase_uuid' => $case->uuid,
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                $contact->notificationNamedConsent = true;
            }),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/places/%s/cases', $place->uuid));

        self::assertEquals(YesNoUnknown::yes()->value, $response->json('data.0.isDeceased'));
    }

    public function testGetPlaceCasesIncludesCauseForConcernStatus(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $case = $this->createCaseForOrganisation($organisation, [
            'date_of_symptom_onset' => CarbonImmutable::now(), // force database-trigger for episode_start_date to this date
        ]);

        $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
            'circumstances' => Circumstances::newInstanceWithVersion(1, static function (Circumstances $circumstances): void {
                $circumstances->causeForConcern = YesNoUnknown::yes();
            }),
            'covidcase_uuid' => $case->uuid,
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                $contact->notificationNamedConsent = true;
            }),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/places/%s/cases', $place->uuid));

        self::assertEquals(YesNoUnknown::yes()->value, $response->json('data.0.causeForConcern'));
    }

    public function testGetPlaceCasesIncludesCauseForConcernStatusWhenCauseForConcernIsNull(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $case = $this->createCaseForOrganisation($organisation, [
            'date_of_symptom_onset' => CarbonImmutable::now(), // force database-trigger for episode_start_date to this date
        ]);

        $this->createContextForPlace($place, [
            'schema_version' => Context::getSchema()->getCurrentVersion()->getVersion(),
            'covidcase_uuid' => $case->uuid,
            'contact' => Contact::newInstanceWithVersion(1, static function (Contact $contact): void {
                $contact->notificationNamedConsent = true;
            }),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/places/%s/cases', $place->uuid));

        self::assertEquals(null, $response->json('data.0.causeForConcern'));
    }

    private function seedGetPlaceCaseData(
        EloquentOrganisation $organisation,
        Place $place,
        int $contextSchemaVersion,
        ?int $symptomOnsetDaysAgo = null,
        ?int $testDaysAgo = null,
    ): EloquentCase {
        $now = CarbonImmutable::now();
        $case = $this->createCaseForOrganisation($organisation, [
            'date_of_symptom_onset' => $symptomOnsetDaysAgo === null ? null : $now->subDays($symptomOnsetDaysAgo),
            'date_of_test' => $testDaysAgo === null ? null : $now->subDays($testDaysAgo),
            'created_at' => $now,
        ]);

        $this->createContextForPlace($place, ['schema_version' => $contextSchemaVersion, 'covidcase_uuid' => $case->uuid]);

        return $case;
    }

    public function testGetPlaceCasesHasVaccinationFields(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForUser($user, [
            // Should ve set to null, else the factory will fill it and the database trigger will change fields incorrectly
            'date_of_test' => null,
            'date_of_symptom_onset' => null,
            // Should be carbon now, if done with faker the application will not find the record
            'created_at' => CarbonImmutable::now(),
        ]);
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // Throw Vaccinaction within case
        $this->putJson(
            '/api/cases/' . $case->uuid . '/fragments/vaccination',
            [
                'isVaccinated' => YesNoUnknown::yes()->value,
                'vaccinationCount' => $vaccinationCount = $this->faker->randomNumber(1),
                'vaccineInjections' => [
                    [
                        'injectionDate' => $vaccinationDate = $this->faker->dateTime()->format('Y-m-d'),
                        'vaccineType' => Vaccine::other()->value,
                        'otherVaccineType' => 'magnetic',
                    ],
                ],
            ],
        );

        $this
            ->getJson("/api/places/{$place->uuid}/cases")
            ->assertSuccessful()
            ->assertJson(
                [
                    'data' => [
                        [
                            'vaccinationCount' => $vaccinationCount,
                            'mostRecentVaccinationDate' => $vaccinationDate,
                        ],
                    ],
                ],
                false,
            );
    }

    public function testGetPlaceCasesHasMomentField(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $this->faker->dateTimeBetween(
                sprintf('-%s days', DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT),
            ),
        ]);
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);
        $moments = Collection::range(1, 5)
            ->map(fn (): string => $this->createMomentForContext($context)->day->format('Y-m-d'))
            ->sort()
            ->values();

        $this
            ->be($user)
            ->getJson("/api/places/{$place->uuid}/cases")
            ->assertSuccessful()
            ->assertJsonIsArray('data')
            ->assertJsonCount(1, 'data')
            ->assertJsonCount($moments->count(), 'data.0.moments');
    }

    public function testGetPlaceCasesHasSectionsFieldIsSetIfSectionIsLinked(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $this->faker->dateTimeBetween(
                sprintf('-%s days', DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT),
            ),
        ]);

        $section = $this->createSectionForPlace($place);
        $this->createContextForCase($case, ['place_uuid' => $place->uuid], [$section]);

        $this
            ->be($user)
            ->getJson("/api/places/{$place->uuid}/cases")
            ->assertSuccessful()
            ->assertJsonPath('data.0.sections.0', $section->label);
    }

    public function testGetPlaceCasesHasSectionFieldIsNullIfNoSectionIsLinked(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $this->faker->dateTimeBetween(
                sprintf('-%s days', DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT),
            ),
        ]);

        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        $this
            ->be($user)
            ->getJson("/api/places/{$place->uuid}/cases")
            ->assertSuccessful()
            ->assertJsonPath('data.0.sections.0', null);
    }

    public function testGetPlace(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlaceForOrganisation($organisation);

        $response = $this->be($user)->getJson("/api/places/{$place->uuid}");
        $response->assertSuccessful();
    }

    /**
     * @param array{hasSymptoms:YesNoUnknown:CarbonImmutable} $expectedSymptomsData
     * @param array{isAdmitted:YesNoUnknown,reason:?HospitalReason} $expectedHospitalData
     */
    #[DataProvider('getPlaceCasesHasSymptomsAndHospitalFieldData')]
    public function testGetPlaceCasesHasSymptomsAndHospitalField(array $expectedSymptomsData, array $expectedHospitalData): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);

        $caseSchemaVersion = EloquentCase::getSchema()->getCurrentVersion();
        $symptomsSchemaVersion = $caseSchemaVersion
            ->getField('symptoms')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();
        $hospitalSchemaVersion = $caseSchemaVersion
            ->getField('hospital')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $this->faker->dateTimeBetween(
                sprintf('-%s days', DbPlaceRepository::GET_CASES_DATE_DIFFERENCE_LIMIT),
            ),
            'symptoms' => Symptoms::newInstanceWithVersion(
                $symptomsSchemaVersion,
                static function (Symptoms $symptoms) use ($expectedSymptomsData): void {
                    $symptoms->hasSymptoms = $expectedSymptomsData['hasSymptoms'];
                },
            ),
            'hospital' => Hospital::newInstanceWithVersion(
                $hospitalSchemaVersion,
                static function (Hospital $hospital) use ($expectedHospitalData): void {
                    $hospital->reason = $expectedHospitalData['reason'];
                    $hospital->isAdmitted = $expectedHospitalData['isAdmitted'];
                },
            ),
        ]);
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        $this
            ->be($user)
            ->getJson("/api/places/{$place->uuid}/cases")
            ->assertSuccessful()
            ->assertJsonIsArray('data')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.symptoms.hasSymptoms', $expectedSymptomsData['hasSymptoms']->value)
            ->assertJsonPath('data.0.hospital.reason', $expectedHospitalData['reason']?->value)
            ->assertJsonPath('data.0.hospital.isAdmitted', $expectedHospitalData['isAdmitted']->value);
    }

    public static function getPlaceCasesHasSymptomsAndHospitalFieldData(): array
    {
        return [
            'both fields data available' => [
                'expectedSymptomsData' => [
                    'hasSymptoms' => YesNoUnknown::yes(),
                ],
                'expectedHospitalData' => [
                    'isAdmitted' => YesNoUnknown::yes(),
                    'reason' => HospitalReason::covid(),
                ],
            ],
            'hospital isAdmitted set to no' => [
                'expectedSymptomsData' => [
                    'hasSymptoms' => YesNoUnknown::no(),
                ],
                'expectedHospitalData' => [
                    'isAdmitted' => YesNoUnknown::no(),
                    'reason' => null,
                ],
            ],
        ];
    }

    public function testGetPlaceCasesHasVaccinationFieldsForOlderCases(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);
        $case = $this->createCaseForUser($user, [
            'schema_version' => 2,
            // Should ve set to null, else the factory will fill it and the database trigger will change fields incorrectly
            'date_of_test' => null,
            'date_of_symptom_onset' => null,
            // Should be carbon now, if done with faker the application will not find the record
            'created_at' => CarbonImmutable::now(),
        ]);
        $this->createContextForCase($case, [
            'place_uuid' => $place->uuid,
        ]);

        // Throw Vaccinaction within case
        $this->be($user)->putJson(
            '/api/cases/' . $case->uuid . '/fragments/vaccination',
            [
                'isVaccinated' => YesNoUnknown::yes()->value,
                'vaccinationCount' => $this->faker->randomNumber(1),
                'vaccineInjections' => [
                    [
                        'injectionDate' => $vaccinationDate = $this->faker->dateTime()->format('Y-m-d'),
                        'vaccineType' => Vaccine::other()->value,
                        'otherVaccineType' => 'magnetic',
                    ],
                ],
            ],
        );

        $response = $this->getJson("/api/places/{$place->uuid}/cases/");
        $response->assertJson([
            'data' => [
                [
                    'vaccinationCount' => 1,
                    'mostRecentVaccinationDate' => $vaccinationDate,
                ]]], false);
    }

    public function testGetPlaceCasesDefaultSorting(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'clusterspecialist');
        $this->be($user);

        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();
        $place = $this->createPlace(['organisation_uuid' => $organisation->uuid]);

        $case1 = $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, 1, null);
        $case2 = $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, null, 2);
        $case3 = $this->seedGetPlaceCaseData($organisation, $place, $contextSchemaVersion, 4, null);

        $response = $this->getJson("/api/places/{$place->uuid}/cases/");

        self::assertEquals([
            $case1->caseId,
            $case2->caseId,
            $case3->caseId,
        ], collect($response->json('data'))->pluck('caseId')->toArray());
    }

    public function testGetPlaceCasesFromDifferentOrganisation(): void
    {
        // Given a clusterspecialist from an organisation
        $user1 = $this->createUser([], 'clusterspecialist');
        $organisation1 = $user1->getOrganisation();

        // And another clusterspecialist from another organisation
        $user2 = $this->createUser([], 'clusterspecialist');
        $organisation2 = $user2->getOrganisation();

        // And a place belonging the first organisation
        $place = $this->createPlace(['organisation_uuid' => $organisation1->uuid]);

        $contextSchemaVersion = Context::getSchema()->getCurrentVersion()->getVersion();

        // And a case from the first organisation related to that place
        $case1 = $this->seedGetPlaceCaseData($organisation1, $place, $contextSchemaVersion, null, null);

        // And a case from the second organisation related to that place
        $case2 = $this->seedGetPlaceCaseData($organisation2, $place, $contextSchemaVersion, null, null);

        // And I am logged in as the clusterspecialist from the first organisation
        $this->be($user1);

        // When I list the cases for the place
        $response = $this->getJson("/api/places/{$place->uuid}/cases/");

        // Then I should see the case belonging to my organisation
        // And I should see the case belonging to the other organisation
        self::assertEqualsCanonicalizing([
            $case1->caseId,
            $case2->caseId,
        ], collect($response->json('data'))->pluck('caseId')->toArray());
    }
}
