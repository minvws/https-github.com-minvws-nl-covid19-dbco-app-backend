<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use Tests\Feature\FeatureTestCase;

class EventTest extends FeatureTestCase
{
    public function testItIsRelatedToAnOrganisationThroughJsonColumnData(): void
    {
        $case = $this->createCase();
        $event = $this->createEvent(['case_uuid' => $case->uuid]);

        self::assertEquals($case->organisation_uuid, $event->refresh()->organisation->uuid);
    }
}
