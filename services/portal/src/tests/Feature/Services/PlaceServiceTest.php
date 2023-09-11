<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\OrganisationType;
use App\Services\PlaceService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Feature\FeatureTestCase;

class PlaceServiceTest extends FeatureTestCase
{
    private PlaceService $placeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->placeService = $this->app->get(PlaceService::class);
    }

    public function testManualResetIndexCount(): void
    {
        $place = $this->createPlace([
            'index_count_reset_at' => null,
        ]);

        $this->createPlaceCountersForPlace($place, [
            'index_count_since_reset' => $this->faker->randomNumber(),
        ]);

        $now = CarbonImmutable::now();
        $this->placeService->resetCount($place, $now);

        $this->assertDatabaseHas('place', [
            'uuid' => $place->uuid,
            'index_count_reset_at' => $now,
        ]);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
            'index_count_since_reset' => 0,
        ]);
    }

    public function testManualResetIndexCountWithoutRecord(): void
    {
        $place = $this->createPlace([
            'index_count_reset_at' => $this->faker->dateTime(),
        ]);

        $now = CarbonImmutable::now();
        $this->placeService->resetCount($place, $now);

        $this->assertDatabaseHas('place', [
            'uuid' => $place->uuid,
            'index_count_reset_at' => $now,
        ]);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
            'index_count' => 0,
            'index_count_since_reset' => 0,
            'last_index_presence' => null,
        ]);
    }

    public function testCalculatePlaceCountersWillReturnArrayIfSuccessful(): void
    {
        $place = $this->createPlace();
        $this->placeService->calculatePlaceCounters($place->uuid);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
        ]);
    }

    public function testCalculatePlaceCountersWillThrowAnExceptionWhenPlaceCannotBeFound(): void
    {
        $this->expectExceptionObject(new ModelNotFoundException());
        $this->placeService->calculatePlaceCounters($this->faker->uuid);
    }

    public function testDetermineOrganisationUuidWithPostalCode(): void
    {
        $zipCode = $this->createZipcode();
        $organisationUuid = $this->placeService->determineOrganisationUuid(null, $zipCode->zipcode);

        $this->assertEquals($organisationUuid, $zipCode->organisation_uuid);
    }

    public function testDetermineOrganisationUuidWithInvalidPostalCode(): void
    {
        $organisationUuid = $this->placeService->determineOrganisationUuid(null, 'invalidZipcode');

        $this->assertNull($organisationUuid);
    }

    public function testDetermineOrganisationUuidWithPlaceAndInvalidPostalCode(): void
    {
        $organisationUuid = $this->placeService->determineOrganisationUuid($this->createPlace(), 'invalidZipcode');

        $this->assertNull($organisationUuid);
    }

    public function testDetermineOrganisationUuidWithNullParameters(): void
    {
        $organisationUuid = $this->placeService->determineOrganisationUuid(null, null);

        $this->assertNull($organisationUuid);
    }

    public function testDetermineOrganisationUuidWithPlaceButNoContextsOrUserOrganisation(): void
    {
        $place = $this->createPlace();

        $organisationUuid = $this->placeService->determineOrganisationUuid($place, null);

        $this->assertNull($organisationUuid);
    }

    public function testDetermineOrganisationUuidWithPlaceAndContexts(): void
    {
        $place = $this->createPlace();
        $context = $this->createContextForPlace($place);

        $organisationUuid = $this->placeService->determineOrganisationUuid($place, null);

        $this->assertEquals($organisationUuid, $context->case->organisation_uuid);
    }

    public function testDetermineOrganisationUuidWithPlaceAndAllowedUserOrganisation(): void
    {
        $organisation = $this->createOrganisation([
            'type' => $this->faker->randomElement([
                OrganisationType::regionalGGD(),
                OrganisationType::demo(),
            ]),
        ]);
        $user = $this->createUserForOrganisation($organisation);
        $place = $this->createPlace();
        $this->be($user);

        $organisationUuid = $this->placeService->determineOrganisationUuid($place, null);

        $this->assertEquals($organisationUuid, $organisation->uuid);
    }

    public function testDetermineOrganisationUuidWithPlaceAndDisallowedUserOrganisation(): void
    {
        $organisation = $this->createOrganisation([
            'type' => $this->faker->randomElement([
                OrganisationType::outsourceOrganisation(),
                OrganisationType::outsourceDepartment(),
                OrganisationType::unknown(),
            ]),
        ]);
        $user = $this->createUserForOrganisation($organisation);
        $place = $this->createPlace();
        $this->be($user);

        $organisationUuid = $this->placeService->determineOrganisationUuid($place, null);

        $this->assertNull($organisationUuid);
    }
}
