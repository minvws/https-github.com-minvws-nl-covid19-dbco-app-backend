<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Place;

use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('place')]
class PlaceCountersTest extends FeatureTestCase
{
    public function testPlaceCountersBelongsToPlace(): void
    {
        $place = $this->createPlace();
        $placeCounters = $this->createPlaceCountersForPlace($place);

        $this->assertEquals($place->uuid, $placeCounters->place->uuid);
    }
}
