<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\ContextCategory;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_merge;
use function sprintf;

#[Group('place')]
class ApiPlaceControllerUpdateTest extends FeatureTestCase
{
    protected function makeRequestData(array $requestAttributes): array
    {
        return array_merge([
            'label' => $this->faker->word(),
            'category' => ContextCategory::accomodatieBinnenland()->value,
        ], $requestAttributes);
    }

    public function testUpdatePostalCodeDoesNotChangeOrganisationIfNoneAvailable(): void
    {
        // Create organisation
        $organisation = $this->createOrganisation();

        // Create zipcode
        $this->createZipcode(['organisation_uuid' => $organisation->uuid, 'zipcode' => '1001AA']);

        // create user for organisation
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        // create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisation->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'address' => [
                'street' => 'streetname',
                'postalCode' => '2002AA',
                'houseNumber' => '205',
                'houseNumberSuffix' => 'su0ffix',
                'town' => 'city',
            ],
        ]));

        // assert status
        $response->assertStatus(200);

        // assert database
        $this->assertDatabaseHas('place', [
            'organisation_uuid' => $organisation->uuid,
            'postalcode' => '2002AA',
        ]);
    }

    public function testUpdateOrganisationUuidDoesNotChangePostalCode(): void
    {
        // Create organisations
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        // Create zipcodes for organisation
        $this->createZipcode(['organisation_uuid' => $organisationOne->uuid, 'zipcode' => '1001AA']);
        $this->createZipcode(['organisation_uuid' => $organisationTwo->uuid, 'zipcode' => '2002AA']);

        // create use for organisation
        $this->be($this->createUserForOrganisation($organisationOne, [], 'clusterspecialist'));

        // Create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'organisationUuid' => $organisationTwo->uuid,
        ]));

        // assert correct status
        $response->assertStatus(200);

        // Assert database
        $this->assertDatabaseHas('place', [
            'organisation_uuid' => $organisationTwo->uuid,
            'postalcode' => '1001AA',
        ]);
    }

    public function testUpdatePostalCodeChangesOrganisationUuidIfNotPresentInRequestData(): void
    {
        // Create organisations
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        // Create zipcodes for organisation
        $this->createZipcode(['organisation_uuid' => $organisationOne->uuid, 'zipcode' => '1001AA']);
        $this->createZipcode(['organisation_uuid' => $organisationTwo->uuid, 'zipcode' => '2002AA']);

        // create use for organisation
        $this->be($this->createUserForOrganisation($organisationOne, [], 'clusterspecialist'));

        // Create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'address' => [
                'street' => 'streetname',
                'postalCode' => '2002AA',
                'houseNumber' => '205',
                'houseNumberSuffix' => 'su0ffix',
                'town' => 'city',
            ],
        ]));

        // assert correct status
        $response->assertStatus(200);

        // Assert database
        $this->assertDatabaseHas('place', [
            'organisation_uuid' => $organisationTwo->uuid,
            'postalcode' => '2002AA',
        ]);
    }

    public function testUpdatePostalCodeDoesNotChangesOrganisationUuidIfPresentInRequestData(): void
    {
        // Create organisations
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        // Create zipcodes for organisation
        $this->createZipcode(['organisation_uuid' => $organisationOne->uuid, 'zipcode' => '1001AA']);
        $this->createZipcode(['organisation_uuid' => $organisationTwo->uuid, 'zipcode' => '2002AA']);

        // create use for organisation
        $this->be($this->createUserForOrganisation($organisationOne, [], 'clusterspecialist'));

        // Create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'organisationUuid' => $organisationOne->uuid,
            'address' => [
                'street' => 'streetname',
                'postalCode' => '2002AA',
                'houseNumber' => '205',
                'houseNumberSuffix' => 'su0ffix',
                'town' => 'city',
            ],
        ]));

        // assert correct status
        $response->assertStatus(200);

        // Assert database
        $this->assertDatabaseHas('place', [
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '2002AA',
        ]);
    }

    public function testUpdatePostalCodeAndOrganisationUuidIfBothArePresentWithinRequestData(): void
    {
        // Create organisations
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();
        $organisationThree = $this->createOrganisation();

        // Create zipcodes for organisation
        $this->createZipcode(['organisation_uuid' => $organisationOne->uuid, 'zipcode' => '1001AA']);
        $this->createZipcode(['organisation_uuid' => $organisationTwo->uuid, 'zipcode' => '2002AA']);
        $this->createZipcode(['organisation_uuid' => $organisationThree->uuid, 'zipcode' => '3003AA']);

        // create use for organisation
        $this->be($this->createUserForOrganisation($organisationOne, [], 'clusterspecialist'));

        // Create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'organisationUuid' => $organisationTwo->uuid,
            'address' => [
                'street' => 'streetname',
                'postalCode' => '3003AA',
                'houseNumber' => '205',
                'houseNumberSuffix' => 'su0ffix',
                'town' => 'city',
            ],
        ]));

        // assert correct status
        $response->assertStatus(200);

        // Assert database
        $this->assertDatabaseHas('place', [
            'organisation_uuid' => $organisationTwo->uuid,
            'postalcode' => '3003AA',
        ]);
    }

    public function testUpdateWithoutDataShouldReportAValidationError(): void
    {
        // Create organisation
        $organisation = $this->createOrganisation();

        // Create zipcode
        $this->createZipcode(['organisation_uuid' => $organisation->uuid, 'zipcode' => '1001AA']);

        // create user for organisation
        $this->be($this->createUserForOrganisation($organisation, [], 'clusterspecialist'));

        // create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisation->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), []);

        // assert status
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'label',
            'category',
        ]);
    }

    public function testUpdateResponseWillSendOrganisationUuidByPostalCodeAndOrganiastionUuid(): void
    {
        // Create organisations
        $organisationOne = $this->createOrganisation();
        $organisationTwo = $this->createOrganisation();

        // Create zipcodes for organisation
        $this->createZipcode(['organisation_uuid' => $organisationOne->uuid, 'zipcode' => '1001AA']);
        $this->createZipcode(['organisation_uuid' => $organisationTwo->uuid, 'zipcode' => '2002AA']);

        // create use for organisation
        $this->be($this->createUserForOrganisation($organisationOne, [], 'clusterspecialist'));

        // Create place for organisation
        $place = $this->createPlace([
            'organisation_uuid' => $organisationOne->uuid,
            'postalcode' => '1001AA',
        ]);

        // make request
        $response = $this->putJson(sprintf('api/places/%s', $place->uuid), $this->makeRequestData([
            'organisationUuid' => $organisationOne->uuid,
            'address' => [
                'street' => 'streetname',
                'postalCode' => '2002AA',
                'houseNumber' => '205',
                'houseNumberSuffix' => 'su0ffix',
                'town' => 'city',
            ],
        ]));

        $response->assertJson([
            'organisationUuid' => $organisationOne->uuid,
            'organisationUuidByPostalCode' => $organisationTwo->uuid,
        ], false);
    }
}
