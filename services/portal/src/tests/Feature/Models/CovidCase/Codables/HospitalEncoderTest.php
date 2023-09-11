<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Hospital;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class HospitalEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $isAdmitted = $this->faker->optional()->randomElement(YesNoUnknown::all());
        $name = $this->faker->optional()->name();
        $location = $this->faker->optional()->sentence();
        $admittedAt = $this->faker->optional()->dateTimeBetween('-3 months');
        $releasedAt = $this->faker->optional()->dateTimeBetween('-3 months');
        $reason = $this->faker->optional()->randomElement(HospitalReason::all());
        $hasGivenPermission = $this->faker->optional()->randomElement(YesNoUnknown::all());
        $practitioner = $this->faker->optional()->sentence();
        $practitionerPhone = $this->faker->optional()->phoneNumber();
        $isInICU = $this->faker->optional()->randomElement(YesNoUnknown::all());
        $admittedInICUAt = $this->faker->optional()->dateTimeBetween('-3 months');

        $hospital = Hospital::getSchema()->getVersion(1)->getTestFactory()->make([
            'isAdmitted' => $isAdmitted,
            'name' => $name,
            'location' => $location,
            'admittedAt' => $admittedAt,
            'releasedAt' => $releasedAt,
            'reason' => $reason,
            'hasGivenPermission' => $hasGivenPermission,
            'practitioner' => $practitioner,
            'practitionerPhone' => $practitionerPhone,
            'isInICU' => $isInICU,
            'admittedInICUAt' => $admittedInICUAt,
        ]);
        $encoded = (new Encoder())->encode($hospital);

        $this->assertEquals($isAdmitted, $encoded->isAdmitted);
        $this->assertEquals($name, $encoded->name);
        $this->assertEquals($location, $encoded->location);
        $this->assertEquals($admittedAt?->format('Y-m-d'), $encoded->admittedAt);
        $this->assertEquals($releasedAt?->format('Y-m-d'), $encoded->releasedAt);
        $this->assertEquals($reason, $encoded->reason);
        $this->assertEquals($hasGivenPermission, $encoded->hasGivenPermission);
        $this->assertEquals($practitioner, $encoded->practitioner);
        $this->assertEquals($practitionerPhone, $encoded->practitionerPhone);
        $this->assertEquals($isInICU, $encoded->isInICU);
        $this->assertEquals($admittedInICUAt?->format('Y-m-d'), $encoded->admittedInICUAt);
    }
}
