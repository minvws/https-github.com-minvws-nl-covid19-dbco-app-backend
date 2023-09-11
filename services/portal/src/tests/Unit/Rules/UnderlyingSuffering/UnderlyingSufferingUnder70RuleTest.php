<?php

declare(strict_types=1);

namespace Tests\Unit\Rules\UnderlyingSuffering;

use App\Rules\UnderlyingSuffering\UnderlyingSufferingUnder70Rule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class UnderlyingSufferingUnder70RuleTest extends TestCase
{
    #[DataProvider('hasUnderlyingSufferingDataProvider')]
    public function testRuleValidationPasses(array $data, ?YesNoUnknown $hasUnderlyingSuffering, bool $shouldPass): void
    {
        $createdAt = CarbonImmutable::parse('today');
        $data = FakerHelper::populateWithDateTimes($this->faker, $data);
        $validator = Validator::make(['hasUnderlyingSuffering' => $hasUnderlyingSuffering?->value], [
            'hasUnderlyingSuffering' => new UnderlyingSufferingUnder70Rule(
                $createdAt,
                CarbonImmutable::parse($data['dateOfBirth']),
            ),
        ]);

        $this->assertEquals($shouldPass, $validator->passes());
    }

    public static function hasUnderlyingSufferingDataProvider(): array
    {
        return [
            'Passes <70, suffer: yes' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::yes(),
                true,
            ],
            'Passes <70, suffer: no' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::no(),
                true,
            ],
            'Passes <70, suffer: unknown' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                YesNoUnknown::unknown(),
                true,
            ],
            'Passes>70, suffer: null' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-71 years')],
                null,
                true,
            ],
            'Passes >70, suffer: unknown' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-71 years')],
                YesNoUnknown::unknown(),
                true,
            ],
            'Passes >70, suffer: null' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-71 years')],
                null,
                true,
            ],
            'Fails <70, suffer: null' => [
                ['dateOfBirth' => FakerHelper::getDateBetween('-69 years', '-40 years')],
                null,
                false,
            ],
            'Fails age:null, suffer: null' => [
                ['dateOfBirth' => null],
                null,
                false,
            ],
        ];
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['hasUnderlyingSuffering' => YesNoUnknown::yes()], [
            'hasUnderlyingSuffering' => new UnderlyingSufferingUnder70Rule(
                CarbonImmutable::parse('today'),
                CarbonImmutable::instance($this->faker->dateTimeBetween('-100 years', '-69 years')),
            ),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleValidationPassesDateOfBirthIsNull(): void
    {
        $createdAt = CarbonImmutable::parse('today');

        $validator = Validator::make(['hasUnderlyingSuffering' => ''], [
            'hasUnderlyingSuffering' => new UnderlyingSufferingUnder70Rule(
                $createdAt,
                null,
            ),
        ]);

        $this->assertTrue($validator->fails());
    }
}
