<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\Place;
use App\Models\Place\ListOptions;
use App\Repositories\DbPlaceRepository;
use Generator;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

class DbPlaceRepositoryTest extends FeatureTestCase
{
    private DbPlaceRepository $dbPlaceRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbPlaceRepository = $this->app->get(DbPlaceRepository::class);
    }

    public function testFindSimilar(): void
    {
        $organisation = $this->createOrganisation();
        $this->createPlace(['town' => 'My Town', 'organisation_uuid' => $organisation->uuid]);

        $listOptions = new ListOptions();
        $res = $this->dbPlaceRepository->searchSimilarPlaces('My Town', $listOptions, $organisation->uuid);
        $this->assertCount(1, $res->items());
    }

    #[DataProvider('isVerifiedFilterDataProvider')]
    public function testFindSimilarByIsVerified(?bool $isVerifiedParam, int $expectedCount): void
    {
        $organisation = $this->createOrganisation();
        $this->createPlace([
            'town' => 'Hometown',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
        ]);
        $this->createPlace([
            'town' => 'Hometown',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
        ]);
        $this->createPlace([
            'town' => 'Hometown',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => false,
        ]);

        $listOptions = new ListOptions();
        if ($isVerifiedParam !== null) {
            $listOptions->isVerified = $isVerifiedParam;
        }
        $res = $this->dbPlaceRepository->searchSimilarPlaces('Hometown', $listOptions, $organisation->uuid);
        $this->assertCount($expectedCount, $res->items());
    }

    public function testOrganisationFilter(): void
    {
        $organisation = $this->createOrganisation();
        $this->createPlace(['town' => 'My Town', 'organisation_uuid' => $organisation->uuid]);

        $listOptions = new ListOptions();
        $res = $this->dbPlaceRepository->searchSimilarPlaces('My Town', $listOptions, '123');
        $this->assertCount(0, $res->items());
    }

    public function testFindSimilarOrdering(): void
    {
        // GIVEN we have Places with all different place counters and properties
        $organisation = $this->createOrganisation();
        $placeF = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
            'updated_at' => '2021-12-01',
        ]);
        $this->createPlaceCountersForPlace($placeF, [
            'index_count_since_reset' => 1,
            'index_count' => 1,
            'last_index_presence' => '2022-12-06',
        ]);
        $placeE = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
            'updated_at' => '2021-12-02',
        ]);
        $this->createPlaceCountersForPlace($placeE, [
            'index_count_since_reset' => 1,
            'index_count' => 1,
            'last_index_presence' => '2022-12-06',
        ]);
        $placeD = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => false,
        ]);
        $this->createPlaceCountersForPlace($placeD, [
            'index_count_since_reset' => 1,
            'index_count' => 1,
            'last_index_presence' => '2022-12-06',
        ]);
        $placeC = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeC, [
            'index_count_since_reset' => 1,
            'index_count' => 1,
            'last_index_presence' => '2022-12-07',
        ]);
        $placeB = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeB, [
            'index_count_since_reset' => 1,
            'index_count' => 2,
        ]);
        $placeA = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeA, [
            'index_count_since_reset' => 2,
        ]);

        // WHEN we query the complete list with default sorting
        $listOptions = new ListOptions();
        $paginator = $this->dbPlaceRepository->searchSimilarPlaces('town', $listOptions, $organisation->uuid);
        /** @var array<Place> $items */
        $items = $paginator->items();

        // THEN the sorting is based on these properties in followin order:
        $this->assertEquals($placeA->uuid, $items[0]->uuid); // index_count_since_reset DESC
        $this->assertEquals($placeB->uuid, $items[1]->uuid); // index_count DESC
        $this->assertEquals($placeC->uuid, $items[2]->uuid); // last_index_presence DESC
        $this->assertEquals($placeD->uuid, $items[3]->uuid); // is_verified ASC
        $this->assertEquals($placeE->uuid, $items[4]->uuid); // updated_at ASC
        $this->assertEquals($placeF->uuid, $items[5]->uuid);
    }

    public function testFindSimilarOrderingWithIndexCountSort(): void
    {
        // GIVEN we have Places with different indexCount
        $organisation = $this->createOrganisation();
        $placeB = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeB, [
            'index_count_since_reset' => 5,
            'index_count' => 100,
        ]);
        $placeA = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
            'updated_at' => '2021-12-02',
        ]);
        $this->createPlaceCountersForPlace($placeA, [
            'index_count_since_reset' => 0,
            'index_count' => 101,
        ]);

        // WHEN we query the complete list with sorting on index count
        $listOptions = new ListOptions();
        $listOptions->sort = 'indexCount';
        $paginator = $this->dbPlaceRepository->searchSimilarPlaces('town', $listOptions, $organisation->uuid);
        /** @var array<Place> $items */
        $items = $paginator->items();

        // THEN the sorting is based on these properties in followin order:
        $this->assertEquals($placeA->uuid, $items[0]->uuid); // index_count_since_reset DESC
        $this->assertEquals($placeB->uuid, $items[1]->uuid); // index_count DESC
    }

    public function testFindSimilarOrderingWithLastIndexPresenceSort(): void
    {
        // GIVEN we have Places with different indexCount
        $organisation = $this->createOrganisation();
        $placeB = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeB, [
            'index_count_since_reset' => 5,
            'last_index_presence' => '2022-12-01',
        ]);
        $placeA = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
            'updated_at' => '2021-12-02',
        ]);
        $this->createPlaceCountersForPlace($placeA, [
            'index_count_since_reset' => 0,
            'last_index_presence' => '2022-12-02',
        ]);

        // WHEN we query the complete list with sorting on index count
        $listOptions = new ListOptions();
        $listOptions->sort = 'lastIndexPresence';
        $paginator = $this->dbPlaceRepository->searchSimilarPlaces('town', $listOptions, $organisation->uuid);
        /** @var array<Place> $items */
        $items = $paginator->items();

        // THEN the sorting is based on these properties in followin order:
        $this->assertEquals($placeA->uuid, $items[0]->uuid); // index_count_since_reset DESC
        $this->assertEquals($placeB->uuid, $items[1]->uuid); // index_count DESC
    }

    public function testFindSimilarOrderingWithLastIndexPresenceAscendingSort(): void
    {
        // GIVEN we have Places with different indexCount
        $organisation = $this->createOrganisation();
        $placeB = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlaceCountersForPlace($placeB, [
            'index_count_since_reset' => 0,
            'index_count' => 0,
            'last_index_presence' => '2022-12-01',
        ]);
        $placeA = $this->createPlace([
            'uuid' => $this->faker->uuid(),
            'town' => 'town',
            'organisation_uuid' => $organisation->uuid,
            'is_verified' => true,
            'updated_at' => '2021-12-02',
        ]);
        $this->createPlaceCountersForPlace($placeA, [
            'index_count_since_reset' => 5,
            'index_count' => 5,
            'last_index_presence' => '2022-12-02',
        ]);

        // WHEN we query the complete list with sorting on index count
        $listOptions = new ListOptions();
        $listOptions->sort = 'indexCount';
        $listOptions->order = 'asc';
        $paginator = $this->dbPlaceRepository->searchSimilarPlaces('town', $listOptions, $organisation->uuid);
        /** @var array<Place> $items */
        $items = $paginator->items();

        // THEN the sorting is based on these properties in followin order:
        $this->assertEquals($placeB->uuid, $items[0]->uuid); // index_count_since_reset DESC
        $this->assertEquals($placeA->uuid, $items[1]->uuid); // index_count DESC
    }

    public function testLookupAddressWithoutHouseNumber(): void
    {
        $postalCode = $this->faker->unique()->postcode;
        $street = $this->faker->streetName;
        $town = $this->faker->city;

        $organisation = $this->createOrganisation();
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize($postalCode),
            'street' => $street,
            'town' => $town,
        ]);

        $address = $this->dbPlaceRepository->lookupAddress($postalCode, null);

        $this->assertIsArray($address);
    }

    public function testLookupAddressWithHouseNumber(): void
    {
        $postalCode = $this->faker->unique()->postcode;
        $street = $this->faker->streetName;
        $town = $this->faker->city;
        $houseNumber = $this->faker->randomNumber(2);

        $organisation = $this->createOrganisation();
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize($postalCode),
            'street' => $street,
            'town' => $town,
            'housenumber' => $houseNumber,
        ]);

        $address = $this->dbPlaceRepository->lookupAddress($postalCode, (string) $houseNumber);

        $this->assertIsArray($address);
    }

    public function testLookupAddressGetsCorrectAddress(): void
    {
        $postalCode = $this->faker->unique()->postcode;
        $street = $this->faker->streetName;
        $town = $this->faker->city;
        $houseNumber = $this->faker->randomNumber(2);

        $organisation = $this->createOrganisation();
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize($postalCode),
            'street' => $street,
            'town' => $town,
            'housenumber' => $houseNumber,
        ]);
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize($this->faker->unique()->postcode),
            'street' => $this->faker->streetName,
            'town' => $this->faker->city,
            'housenumber' => $this->faker->randomNumber(3),
        ]);

        $address = $this->dbPlaceRepository->lookupAddress($postalCode, (string) $houseNumber);

        $this->assertEquals($address, [
            'street' => $street,
            'town' => $town,
        ]);
    }

    public function testLookupAddressNotFound(): void
    {
        $organisation = $this->createOrganisation();
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize('0000AA'),
            'street' => $this->faker->streetName,
            'town' => $this->faker->city,
        ]);

        $address = $this->dbPlaceRepository->lookupAddress('1111BB', null);

        $this->assertNull($address);
    }

    public function testSearchInCategory(): void
    {
        $organisation = $this->createOrganisation();
        $this->createPlaceForOrganisation($organisation, [
            'postalcode' => PostalCodeHelper::normalize('0000AA'),
            'street' => $this->faker->streetName,
            'town' => $this->faker->city,
            'category' => ContextCategory::bruiloft(),
        ]);

        $all = $this->dbPlaceRepository->searchSimilarPlaces('', new ListOptions(), $organisation->uuid);
        $otherCategory = $this->dbPlaceRepository->searchSimilarPlaces(
            '',
            new ListOptions(),
            $organisation->uuid,
            [ContextCategory::begeleid()],
        );
        $thisCategory = $this->dbPlaceRepository->searchSimilarPlaces(
            '',
            new ListOptions(),
            $organisation->uuid,
            [ContextCategory::bruiloft()],
        );

        $this->assertCount(1, $all->items());
        $this->assertCount(0, $otherCategory->items());
        $this->assertCount(1, $thisCategory->items());
    }

    public static function isVerifiedFilterDataProvider(): Generator
    {
        yield 'both verified and unverified places (no verified filter specified)' => [null, 3];
        yield 'only verified places' => [true, 2];
        yield 'only unverified places' => [false, 1];
    }
}
