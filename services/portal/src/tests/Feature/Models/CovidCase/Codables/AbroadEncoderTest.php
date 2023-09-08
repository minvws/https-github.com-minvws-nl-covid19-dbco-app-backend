<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Abroad;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function count;

class AbroadEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $wasAbroad = $this->faker->randomElement(YesNoUnknown::all());
        $abroad = $this->createFragment(Abroad::class, [
            'wasAbroad' => $wasAbroad,
        ]);

        $encoded = (new Encoder())->encode($abroad);

        $this->assertEquals($wasAbroad, $encoded->wasAbroad);
        $this->assertCount(count($abroad->trips), $encoded->trips);
    }
}
