<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\EduDaycare;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\EduDaycareType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class EduDaycareEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $isStudent = $this->faker->randomElement(YesNoUnknown::all());
        $type = $this->faker->randomElement(EduDaycareType::all());

        $eduDaycare = EduDaycare::getSchema()->getVersion(1)->getTestFactory()->make([
            'isStudent' => $isStudent,
            'type' => $type,
        ]);
        $encoded = (new Encoder())->encode($eduDaycare);

        $this->assertEquals($isStudent, $encoded->isStudent);
        $this->assertEquals($type, $encoded->type);
    }
}
