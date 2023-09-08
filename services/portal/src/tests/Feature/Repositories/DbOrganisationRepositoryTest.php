<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\DbOrganisationRepository;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

class DbOrganisationRepositoryTest extends FeatureTestCase
{
    private DbOrganisationRepository $organisationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisationRepository = new DbOrganisationRepository();
    }

    public function testGetOrganisationByUuidFound(): void
    {
        $organisation = $this->createOrganisation();

        $response = $this->organisationRepository->getEloquentOrganisationByUuid($organisation->uuid);
        $this->assertEquals($organisation->uuid, $response->uuid);
    }

    public function testGetOrganisationByUuidNotFound(): void
    {
        $response = $this->organisationRepository->getEloquentOrganisationByUuid($this->faker->uuid);
        $this->assertNull($response);
    }

    public function testListOrganisationUuidsSelectsOnlyUuid(): void
    {
        $this->createOrganisation();

        $collection = $this->organisationRepository->listOrganisationUuids();
        $firstUuid = $collection->first();

        $this->assertTrue(Uuid::isValid($firstUuid));
    }
}
