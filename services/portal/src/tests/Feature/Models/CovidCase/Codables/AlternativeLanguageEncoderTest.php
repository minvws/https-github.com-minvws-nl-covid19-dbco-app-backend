<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase\Codables;

use App\Models\CovidCase\AlternativeLanguage;
use MinVWS\Codable\Encoder;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

class AlternativeLanguageEncoderTest extends FeatureTestCase
{
    public function testEncode(): void
    {
        $useAlternativeLanguage = $this->faker->randomElement(YesNoUnknown::all());
        $emailLanguage = $this->faker->randomElement(EmailLanguage::all());
        $phoneLanguages = $this->faker->randomElements(Language::all());

        $alternativeLanguage = $this->createFragment(AlternativeLanguage::class, [
            'useAlternativeLanguage' => $useAlternativeLanguage,
            'emailLanguage' => $emailLanguage,
            'phoneLanguages' => $phoneLanguages,
        ]);

        $encoded = (new Encoder())->encode($alternativeLanguage);

        $this->assertEquals($useAlternativeLanguage, $encoded->useAlternativeLanguage);
        $this->assertEquals($emailLanguage, $encoded->emailLanguage);
        $this->assertEquals($phoneLanguages, $encoded->phoneLanguages);
    }
}
