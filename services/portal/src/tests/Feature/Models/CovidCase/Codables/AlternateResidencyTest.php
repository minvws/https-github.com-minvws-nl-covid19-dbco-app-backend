<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\AlternateResidency;
use App\Models\Shared\Address;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class AlternateResidencyTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasAlternateResidency = $this->faker->randomElement(YesNoUnknown::all());
        $remark = $this->faker->text();

        $address = Address::getSchema()->getVersion(1)->getTestFactory()->make();

        $alternativeLanguage = $this->createFragment(AlternateResidency::class, [
            'hasAlternateResidency' => $hasAlternateResidency,
            'remark' => $remark,
            'address' => $address,
        ]);

        $encoded = (new Encoder())->encode($alternativeLanguage);

        $this->assertEquals($hasAlternateResidency, $encoded->hasAlternateResidency);
        $this->assertEquals($remark, $encoded->remark);

        $this->assertEquals($address->postalCode, $encoded->address->postalCode);
        $this->assertEquals($address->houseNumber, $encoded->address->houseNumber);
        $this->assertEquals($address->houseNumberSuffix, $encoded->address->houseNumberSuffix);
        $this->assertEquals($address->street, $encoded->address->street);
        $this->assertEquals($address->town, $encoded->address->town);
    }
}
