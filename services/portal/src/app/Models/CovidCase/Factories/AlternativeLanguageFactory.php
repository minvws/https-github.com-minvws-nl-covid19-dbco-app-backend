<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class AlternativeLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'useAlternativeLanguage' => $this->faker->randomElement(YesNoUnknown::all()),
            'emailLanguage' => $this->faker->randomElement(EmailLanguage::all()),
            'phoneLanguages' => $this->faker->randomElements(Language::all()),
        ];
    }
}
