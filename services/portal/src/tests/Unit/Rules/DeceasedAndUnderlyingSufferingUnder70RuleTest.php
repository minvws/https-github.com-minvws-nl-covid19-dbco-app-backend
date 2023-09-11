<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\DeceasedAndUnderlyingSufferingUnder70Rule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class DeceasedAndUnderlyingSufferingUnder70RuleTest extends TestCase
{
    #[DataProvider('hasUnderlyingSufferingDataProvider')]
    public function testRuleValidationPasses(?YesNoUnknown $isDeceased, array $data, ?YesNoUnknown $hasUnderlyingSuffering, bool $shouldPass): void
    {
        $createdAt = CarbonImmutable::parse('today');
        $data = FakerHelper::populateWithDateTimes($this->faker, $data);
        $validator = Validator::make(['isDeceased' => $isDeceased?->value], [
            'isDeceased' => new DeceasedAndUnderlyingSufferingUnder70Rule(
                $createdAt,
                CarbonImmutable::parse($data['dateOfBirth']),
                $hasUnderlyingSuffering,
            ),
        ]);

        $this->assertEquals($shouldPass, $validator->passes());
    }

    public static function hasUnderlyingSufferingDataProvider(): array
    {
        return [
            'Passes Deceased: no, <70, suffer: yes' => [
                YesNoUnknown::no(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::yes(),
                true,
            ],
            'Passes Deceased: yes, <70, suffer: yes' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::yes(),
                true,
            ],
            'Passes Deceased: yes, <70, suffer: no' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::no(),
                true,
            ],
            'Passes Deceased: yes, <70, suffer: unknown' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::unknown(),
                true,
            ],
            'Passes Deceased: yes, >70, suffer: null' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-71 years')],
                null,
                true,
            ],
            'Passes Deceased: null, >70, suffer: unknown' => [
                null,
                ['dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-71 years')],
                YesNoUnknown::unknown(),
                true,
            ],
            'Fails Deceased: yes, <70, suffer: null' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                null,
                false,
            ],
            'Fails Deceased: yes, age:null, suffer: null' => [
                YesNoUnknown::yes(),
                ['dateOfBirth' => null],
                null,
                false,
            ],
        ];
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['isDeceased' => YesNoUnknown::yes()], [
            'isDeceased' => new DeceasedAndUnderlyingSufferingUnder70Rule(
                CarbonImmutable::parse('today'),
                CarbonImmutable::instance($this->faker->dateTimeBetween('-100 years', '-69 years')),
                YesNoUnknown::yes(),
            ),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleValidationPassesDateOfBirthIsNull(): void
    {
        $createdAt = CarbonImmutable::parse('today');

        $validator = Validator::make(['isDeceased' => YesNoUnknown::yes()->value], [
            'isDeceased' => new DeceasedAndUnderlyingSufferingUnder70Rule(
                $createdAt,
                null,
                null,
            ),
        ]);

        $this->assertEquals(false, $validator->passes());
    }
}
