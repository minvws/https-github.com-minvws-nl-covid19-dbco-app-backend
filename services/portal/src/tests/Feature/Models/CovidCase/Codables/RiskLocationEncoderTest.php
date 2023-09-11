<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\RiskLocation;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class RiskLocationEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $isLivingAtRiskLocation = $this->faker->randomElement(YesNoUnknown::all());
        $type = $this->faker->randomElement(RiskLocationType::all());
        $otherType = $this->faker->optional()->paragraph();

        $eduDaycare = RiskLocation::getSchema()->getVersion(1)->getTestFactory()->make([
            'isLivingAtRiskLocation' => $isLivingAtRiskLocation,
            'type' => $type,
            'otherType' => $otherType,
        ]);
        $encoded = (new Encoder())->encode($eduDaycare);

        $this->assertEquals($isLivingAtRiskLocation, $encoded->isLivingAtRiskLocation);
        $this->assertEquals($type, $encoded->type);
        $this->assertEquals($otherType, $encoded->otherType);
    }
}
