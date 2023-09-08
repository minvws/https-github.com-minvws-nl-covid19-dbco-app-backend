<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Tests\Feature\FeatureTestCase;

class EventCaseOrganisationUUIDLinkTriggerTest extends FeatureTestCase
{
    public function testTriggerAddsOrganisationIdToTheEvent(): void
    {
        $org = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($org);
        $event = $this->createEvent([
            'case_uuid' => $case->uuid,
        ]);

        $this->assertDatabaseHas('event', [
            'uuid' => $event->uuid,
            'organisation_uuid' => $org->uuid,
        ]);
    }
}
