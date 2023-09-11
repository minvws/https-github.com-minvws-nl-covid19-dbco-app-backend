<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Communication;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class CommunicationEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $otherAdviceGiven = $this->faker->text(5000);
        $particularities = $this->faker->text(5000);
        $scientificResearchConsent = $this->faker->randomElement(YesNoUnknown::all());
        $remarksRivm = $this->faker->text(5000);

        //generate a configured Communication object
        $communication = $this->createFragment(Communication::class, [
            'otherAdviceGiven' => $otherAdviceGiven,
            'particularities' => $particularities,
            'scientificResearchConsent' => $scientificResearchConsent,
            'remarksRivm' => $remarksRivm,
        ]);

        //encode the Communication object
        $encoded = (new Encoder())->encode($communication);

        //check that the encoded object has the same values as the original object
        $this->assertEquals($otherAdviceGiven, $encoded->otherAdviceGiven);
        $this->assertEquals($particularities, $encoded->particularities);
        $this->assertEquals($scientificResearchConsent, $encoded->scientificResearchConsent);
        $this->assertEquals($remarksRivm, $encoded->remarksRivm);
    }
}
