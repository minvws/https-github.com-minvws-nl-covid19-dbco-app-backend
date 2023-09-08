<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Illuminate\Testing\PendingCommand;
use Tests\Feature\FeatureTestCase;

class PlaceOrganisationLinkerTest extends FeatureTestCase
{
    public function testRunCommand(): void
    {
        $organisation = $this->createOrganisation();
        $this->createZipcode([
            'zipcode' => '1122AB',
            'organisation_uuid' => $organisation->uuid,
        ]);
        $this->createPlace([
            'postalcode' => '1122AB',
        ]);

        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('place:link-organisations');

        $artisan
            ->assertExitCode(0)
            ->execute();

        $this->assertDatabaseHas('place', [
            'postalcode' => '1122AB',
            'organisation_uuid' => $organisation->uuid,
        ]);
    }
}
