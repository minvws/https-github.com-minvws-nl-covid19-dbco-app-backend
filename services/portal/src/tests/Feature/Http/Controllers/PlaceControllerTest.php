<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\FeatureTestCase;

class PlaceControllerTest extends FeatureTestCase
{
    public function testPlaceEditPageView(): void
    {
        // - Create organisation & user that will be logged in
        // Organisation is needed as the place needs to be
        // linked to the organisation in order to edit
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        // Create place for organisation
        $place = $this->createPlaceForOrganisation($organisation);

        // Make the call to the given page
        $response = $this->get("/editplace/{$place->uuid}");

        // Make sure that the correct view with data is returned
        $response->assertViewIs('editplace');
        $response->assertViewHas('place');
    }
}
