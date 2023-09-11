<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\PrincipalContextualSettings;
use MinVWS\Codable\Encoder;
use Tests\Feature\FeatureTestCase;

use function explode;

class PrincipalContextualSettingsEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasPrincipalContextualSettings = $this->faker->boolean();
        $items = explode(' ', $this->faker->sentence(5));

        $principalContextualSettings = PrincipalContextualSettings::getSchema()->getVersion(1)->getTestFactory()->make([
            'hasPrincipalContextualSettings' => $hasPrincipalContextualSettings,
            'items' => $items,
            'otherItems' => [],
        ]);
        $encoded = (new Encoder())->encode($principalContextualSettings);

        $this->assertEquals($hasPrincipalContextualSettings, $encoded->hasPrincipalContextualSettings);
        $this->assertEquals($items, $encoded->items);
        $this->assertEquals([], $encoded->otherItems);
    }
}
