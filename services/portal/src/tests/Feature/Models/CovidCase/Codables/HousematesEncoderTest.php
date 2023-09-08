<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Housemates;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class HousematesEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasHouseMates = YesNoUnknown::yes();
        $hasOwnFacilities = $this->faker->boolean;
        $hasOwnKitchen = $this->faker->boolean;
        $hasOwnBedroom = $this->faker->boolean;
        $hasOwnRestroom = $this->faker->boolean;
        $canStrictlyIsolate = $this->faker->boolean;
        $bottlenecks = $this->faker->optional()->text;

        $housemates = $this->createFragment(Housemates::class, [
            'hasHouseMates' => $hasHouseMates,
            'hasOwnFacilities' => $hasOwnFacilities,
            'hasOwnKitchen' => $hasOwnKitchen,
            'hasOwnBedroom' => $hasOwnBedroom,
            'hasOwnRestroom' => $hasOwnRestroom,
            'canStrictlyIsolate' => $canStrictlyIsolate,
            'bottlenecks' => $bottlenecks,
        ]);
        $encoded = (new Encoder())->encode($housemates);

        //assert encoded data matches the data we created the fragment with
        $this->assertEquals($hasHouseMates->value, $encoded->hasHouseMates);
        $this->assertEquals($hasOwnFacilities, $encoded->hasOwnFacilities);
        $this->assertEquals($hasOwnKitchen, $encoded->hasOwnKitchen);
        $this->assertEquals($hasOwnBedroom, $encoded->hasOwnBedroom);
        $this->assertEquals($hasOwnRestroom, $encoded->hasOwnRestroom);
        $this->assertEquals($canStrictlyIsolate, $encoded->canStrictlyIsolate);
        $this->assertEquals($bottlenecks, $encoded->bottlenecks);
    }
}
