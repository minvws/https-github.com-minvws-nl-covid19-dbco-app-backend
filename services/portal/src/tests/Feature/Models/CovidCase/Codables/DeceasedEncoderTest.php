<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\Deceased;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class DeceasedEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $deceased = $this->createFragment(Deceased::class, [
            'isDeceased' => YesNoUnknown::yes(),
            'deceasedAt' => $this->faker->dateTime(),
            'cause' => CauseOfDeath::other(),
        ]);

        $encoded = (new Encoder())->encode($deceased);

        self::assertEquals($deceased->isDeceased, $encoded->isDeceased);
        self::assertEquals($deceased->deceasedAt->format('Y-m-d'), $encoded->deceasedAt);
        self::assertEquals($deceased->cause, $encoded->cause);
    }
}
