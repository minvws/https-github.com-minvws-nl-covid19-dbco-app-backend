<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\RecentBirth;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class RecentBirthEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasRecentlyGivenBirth = $this->faker->randomElement(YesNoUnknown::all());
        $birthDate = $this->faker->dateTimeBetween('-3 months');
        $birthRemarks = $this->faker->optional()->paragraph();

        $recentBirth = RecentBirth::getSchema()->getVersion(1)->getTestFactory()->make([
            'hasRecentlyGivenBirth' => $hasRecentlyGivenBirth,
            'birthDate' => $birthDate,
            'birthRemarks' => $birthRemarks,
        ]);
        $encoded = (new Encoder())->encode($recentBirth);

        $this->assertEquals($hasRecentlyGivenBirth, $encoded->hasRecentlyGivenBirth);
        $this->assertEquals($birthDate->format('Y-m-d'), $encoded->birthDate);
        $this->assertEquals($birthRemarks, $encoded->birthRemarks);
    }
}
