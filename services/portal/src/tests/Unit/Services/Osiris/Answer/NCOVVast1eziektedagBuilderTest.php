<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVVast1eziektedagBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVVast1eziektedagBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVVast1eziektedagBuilderTest extends TestCase
{
    use AssertAnswers;

    #[Dataprovider('exampleDataprovider')]
    public function testBuilderAnswer(
        ?YesNoUnknown $hasSymptoms,
        array $symptoms,
        ?string $dateOfSymptomsOnset,
        bool $isEstimated,
        string $answerValue,
    ): void {
        $dateOfSymptomOnset = $dateOfSymptomsOnset === null ? null : CarbonImmutable::parse($dateOfSymptomsOnset);
        $case = $this->createCase($dateOfSymptomOnset, $isEstimated, $hasSymptoms, $symptoms);

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVVast1eziektedag', $answerValue));
    }

    public static function exampleDataprovider(): array
    {
        return [
            "YesNoUnknown::self::yes(), nothing, null, false, 'onb'" => [
                YesNoUnknown::yes(),
                [],
                null,
                false,
                'onb',
            ],
            "YesNoUnknown::self::yes(), nothing, null, true, 'onb'" => [
                YesNoUnknown::yes(),
                [],
                null,
                true,
                'onb',
            ],
            "YesNoUnknown::self::yes(), nothing, '13-01-1995', false, 'V'" => [
                YesNoUnknown::yes(),
                [],
                '13-01-1995',
                false,
                'V',
            ],
            "YesNoUnknown::self::yes(), nothing, '13-01-1995', true, 'G', " => [
                YesNoUnknown::yes(),
                [],
                '13-01-1995',
                true,
                'G',
            ],

            "YesNoUnknown::self::yes(), 1 or more, null, false, 'onb'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                null,
                false,
                'onb',
            ],
            "YesNoUnknown::self::yes(), 1 or more, null, true, 'onb'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                null,
                true,
                'onb',
            ],
            "YesNoUnknown::self::yes(), 1 or more, '13-01-1995', false, 'V'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                '13-01-1995',
                false,
                'V',
            ],
            "YesNoUnknown::self::yes(), 1 or more, '13-01-1995', true, 'G'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                '13-01-1995',
                true,
                'G',
            ],

            "null, nothing, null, false, 'onb'" => [
                null,
                [],
                null,
                false,
                'onb',
            ],
            "null, nothing, null, true, 'onb'" => [
                null,
                [],
                null,
                true,
                'onb',
            ],
            "null, nothing, '13-01-1995', false, 'V'" => [
                null,
                [],
                '13-01-1995',
                false,
                'V',
            ],
            "null, nothing, '13-01-1995', true, 'G'" => [
                null,
                [],
                '13-01-1995',
                true,
                'G',
            ],

            "YesNoUnknown::unknown(), nothing, null, false, 'onb'" => [
                YesNoUnknown::unknown(),
                [],
                null,
                false,
                'onb',
            ],
            "YesNoUnknown::unknown(), nothing, null, true, 'onb'" => [
                YesNoUnknown::unknown(),
                [],
                null,
                true,
                'onb',
            ],
            "YesNoUnknown::unknown(), nothing, '13-01-1995', false, 'V'" => [
                YesNoUnknown::unknown(),
                [],
                '13-01-1995',
                false,
                'V',
            ],
            "YesNoUnknown::unknown(), nothing, '13-01-1995', true, 'G'" => [
                YesNoUnknown::unknown(),
                [],
                '13-01-1995',
                true,
                'G',
            ],
            "YesNoUnknown::no(), nothing, null, false, 'NVT'" => [
                YesNoUnknown::no(),
                [],
                null,
                false,
                'NVT',
            ],
        ];
    }

    public function createCase(
        ?DateTimeInterface $dateOfSymptomOnset,
        ?bool $isSymptomOnsetEstimated,
        ?YesNoUnknown $hasSymptoms,
        ?array $symptoms,
    ): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        assert(isset($case->test));
        assert($case->test instanceof Test);
        $case->test->dateOfSymptomOnset = $dateOfSymptomOnset;
        $case->test->isSymptomOnsetEstimated = $isSymptomOnsetEstimated;
        assert($case->symptoms instanceof Symptoms);

        $case->symptoms->hasSymptoms = $hasSymptoms;
        if ($case->symptoms->hasSymptoms === YesNoUnknown::yes()) {
            $case->symptoms->symptoms = $symptoms;
        }
        return $case;
    }
}
