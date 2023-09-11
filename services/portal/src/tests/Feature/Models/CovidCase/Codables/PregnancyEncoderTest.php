<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Codables\PregnancyEncoder;
use App\Models\CovidCase\Pregnancy;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class PregnancyEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $isPregnant = $this->faker->randomElement(YesNoUnknown::all());
        $dueDate = $this->faker->dateTimeBetween('-3 months');

        $Pregnancy = Pregnancy::getSchema()->getVersion(1)->getTestFactory()->make([
            'isPregnant' => $isPregnant,
            'dueDate' => $dueDate,
        ]);
        $encoded = (new Encoder())->encode($Pregnancy);

        $this->assertEquals($isPregnant, $encoded->isPregnant);
        $this->assertEquals($dueDate->format('Y-m-d'), $encoded->dueDate);
    }

    public function testV2EncodeWithAllParameters(): void
    {
        $isPregnant = $this->faker->randomElement(YesNoUnknown::all());
        $remarks = $this->faker->paragraph;

        $Pregnancy = Pregnancy::getSchema()->getVersion(2)->getTestFactory()->make([
            'isPregnant' => $isPregnant,
            'remarks' => $remarks,
        ]);
        $encoded = (new Encoder())->encode($Pregnancy);

        $this->assertEquals($isPregnant, $encoded->isPregnant);
        $this->assertEquals($remarks, $encoded->remarks);
    }

    public function testV2EncodeWithEmptyRemarks(): void
    {
        $isPregnant = $this->faker->randomElement(YesNoUnknown::all());
        $remarks = null;

        $Pregnancy = Pregnancy::getSchema()->getVersion(2)->getTestFactory()->make([
            'isPregnant' => $isPregnant,
            'remarks' => $remarks,
        ]);
        $encoded = (new Encoder())->encode($Pregnancy);

        $this->assertEquals($isPregnant, $encoded->isPregnant);
        $this->assertEquals($remarks, $encoded->remarks);
    }

    public function testV2EncodeJson(): void
    {
        $isPregnant = YesNoUnknown::yes();
        $remarks = $this->faker->paragraph;

        $Pregnancy = Pregnancy::getSchema()->getVersion(2)->getTestFactory()->make([
            'isPregnant' => $isPregnant,
            'remarks' => $remarks,
        ]);

        $encoder = new JSONEncoder();
        $encoder->getContext()->registerDecorator(Pregnancy::class, new PregnancyEncoder());
        $json = $encoder->encode($Pregnancy);

        $this->assertJson($json);
    }
}
