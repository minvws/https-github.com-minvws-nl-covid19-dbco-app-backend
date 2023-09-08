<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\ZIE1eZiekteDtBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(ZIE1eZiekteDtBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class ZIE1eZiekteDtBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('exampleDataprovider')]
    public function testBuilderAnswer(
        ?YesNoUnknown $hasSymptoms,
        ?array $symptoms,
        ?string $dateOfSymptomOnset,
        ?bool $isEstimated,
        ?string $expectedDateOfSymptomOnset,
    ): void {
        $case = $this->createCase($dateOfSymptomOnset, $isEstimated, $hasSymptoms);

        $case->symptoms = new Symptoms();
        $case->symptoms->symptoms = $symptoms;
        $case = $this->createCase($dateOfSymptomOnset, $isEstimated, $hasSymptoms);

        $this->answersForCase($case)->assertAnswer(new Answer('ZIE1eZiekteDt', $expectedDateOfSymptomOnset));
    }

    #[DataProvider('exampleDataproviderWithNoAnswers')]
    public function testBuilderAnswerHasNoAnswer(
        ?YesNoUnknown $hasSymptoms,
        ?array $symptoms,
        ?string $dateOfSymptomOnset,
        ?bool $isEstimated,
    ): void {
        $case = $this->createCase($dateOfSymptomOnset, $isEstimated, $hasSymptoms);

        $case->symptoms = new Symptoms();
        $case->symptoms->symptoms = $symptoms;
        $case = $this->createCase($dateOfSymptomOnset, $isEstimated, $hasSymptoms);

        $this->answersForCase($case)->assertEmpty();
    }

    public static function exampleDataprovider(): array
    {
        return [
            "YesNoUnknown::self::yes(), [], '13-01-1995', false, '13-01-1995'" => [
                YesNoUnknown::yes(),
                [],
                '13-01-1995',
                false,
                '13-01-1995',
            ],
            "YesNoUnknown::self::yes(), [], '13-01-1995', true, '13-01-1995'" => [
                YesNoUnknown::yes(),
                [],
                '13-01-1995',
                true,
                '13-01-1995',
            ],

            "YesNoUnknown::self::yes(), 1 or more, '13-01-1995', false, '13-01-1995'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                '13-01-1995',
                false,
                '13-01-1995',
            ],
            "YesNoUnknown::self::yes(), 1 or more, '13-01-1995', true, '13-01-1995'" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                '13-01-1995',
                true,
                '13-01-1995',
            ],

            "null, [], '13-01-1995', false, '13-01-1995'" => [
                null,
                [],
                '13-01-1995',
                false,
                '13-01-1995',
            ],
            "null, [], '13-01-1995', true, '13-01-1995'" => [
                null,
                [],
                '13-01-1995',
                true,
                '13-01-1995',
            ],

            "YesNoUnknown::unknown(), [], '13-01-1995', false, '13-01-1995'" => [
                YesNoUnknown::unknown(),
                [],
                '13-01-1995',
                false,
                '13-01-1995',
            ],
            "YesNoUnknown::unknown(), [], '13-01-1995', true, '13-01-1995'" => [
                YesNoUnknown::unknown(),
                [],
                '13-01-1995',
                true,
                '13-01-1995',
            ],
        ];
    }

    public static function exampleDataproviderWithNoAnswers(): array
    {
        return [
            "YesNoUnknown::self::yes(), [], null, false, null" => [
                YesNoUnknown::yes(),
                [],
                null,
                false,
                null,
            ],
            "YesNoUnknown::self::yes(), [], null, true, null" => [
                YesNoUnknown::yes(),
                [],
                null,
                true,
                null,
            ],
            "YesNoUnknown::self::yes(), 1 or more, null, false, null" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                null,
                false,
                null,
            ],
            "YesNoUnknown::self::yes(), 1 or more, null, true, null" => [
                YesNoUnknown::yes(),
                [
                    Symptom::cough(),
                    Symptom::fever(),
                    Symptom::soreThroat(),
                ],
                null,
                true,
                null,
            ],
            "null, [], null, false, null" => [
                null,
                [],
                null,
                false,
                null,
            ],
            "null, [], null, true, null" => [
                null,
                [],
                null,
                true,
                null,
            ],
            "YesNoUnknown::unknown(), [], null, false, null" => [
                YesNoUnknown::unknown(),
                [],
                null,
                false,
                null,
            ],
            "YesNoUnknown::unknown(), [], null, true, null" => [
                YesNoUnknown::unknown(),
                [],
                null,
                true,
                null,
            ],
            "YesNoUnknown::no(), [], null, false, null" => [
                YesNoUnknown::no(),
                [],
                null,
                false,
                null,
            ],
        ];
    }

    private function createCase(
        ?string $dateOfSymptomOnset,
        ?bool $isSymptomOnsetEstimated,
        ?YesNoUnknown $hasSymptoms,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        assert(isset($case->test));
        assert($case->test instanceof Test);
        $case->test->dateOfSymptomOnset = $dateOfSymptomOnset;
        $case->test->isSymptomOnsetEstimated = $isSymptomOnsetEstimated;
        assert($case->symptoms instanceof Symptoms);
        $case->symptoms->hasSymptoms = $hasSymptoms;
        return $case;
    }
}
