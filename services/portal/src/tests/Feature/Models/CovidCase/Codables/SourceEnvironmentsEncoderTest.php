<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\SourceEnvironments;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function count;

class SourceEnvironmentsEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $hasLikelySourceEnvironments = $this->faker->randomElement(YesNoUnknown::all());
        $likelySourceEnvironments = $this->faker->randomElements(
            ContextCategory::all(),
            $this->faker->numberBetween(0, count(ContextCategory::all())),
        );

        $eduDaycare = SourceEnvironments::getSchema()->getVersion(1)->getTestFactory()->make([
            'hasLikelySourceEnvironments' => $hasLikelySourceEnvironments,
            'likelySourceEnvironments' => $likelySourceEnvironments,
        ]);
        $encoded = (new Encoder())->encode($eduDaycare);

        $this->assertEquals($hasLikelySourceEnvironments, $encoded->hasLikelySourceEnvironments);
        $this->assertEquals($likelySourceEnvironments, $encoded->likelySourceEnvironments);
    }
}
