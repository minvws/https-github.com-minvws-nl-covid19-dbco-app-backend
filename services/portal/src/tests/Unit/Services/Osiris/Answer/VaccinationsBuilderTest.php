<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Schema\Types\SchemaType;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\VaccinationsBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(VaccinationsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class VaccinationsBuilderTest extends TestCase
{
    use AssertAnswers;

    private const CASE_VERSION = 3; // NOTE: you can't change this without changing some assertions below as well!

    public function testVaccineMappings(): void
    {
        foreach (Vaccine::all() as $vaccineType) {
            $case = $this->createCase(YesNoUnknown::yes(), [self::createVaccineInjection($vaccineType)]);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid vaccine mapping for ' . $vaccineType->label);
            $this->assertEquals('NCOVvacmerk1', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
        }
    }

    public static function vaccineInjectionsProvider(): Generator
    {
        yield 'No vaccine injections' => [
            YesNoUnknown::no(),
            [],
            [],
        ];

        yield 'No vaccine injections, but injections filled' => [
            YesNoUnknown::no(),
            [
                self::createVaccineInjection(Vaccine::pfizer(), null, CarbonImmutable::make('April 15th 2022')),
            ],
            [],
        ];

        yield 'Vaccine injections unknown' => [
            YesNoUnknown::unknown(),
            [],
            [],
        ];

        yield 'Vaccine injections unknown (null)' => [
            null,
            [],
            [],
        ];

        yield 'Single pfizer injection April 15th 2022' => [
            YesNoUnknown::yes(),
            [
                self::createVaccineInjection(Vaccine::pfizer(), null, CarbonImmutable::make('April 15th 2022')),
            ],
            [
                new Answer('NCOVvacmerk1', '1'),
                new Answer('NCOVpatvac1Dt', '15-04-2022'),
            ],
        ];

        yield 'Multiple injections, no dates' => [
            YesNoUnknown::yes(),
            [
                self::createVaccineInjection(Vaccine::janssen()),
                self::createVaccineInjection(Vaccine::pfizer()),
                self::createVaccineInjection(Vaccine::pfizer()),
            ],
            [
                new Answer('NCOVvacmerk1', '4'),
                new Answer('NCOVvacmerk2', '1'),
                new Answer('NCOVvacmerk3', '1'),
                new Answer('NCOVafweervac3', 'Onb'),
            ],
        ];

        yield 'Fallback to other if vaccine type is not possible for a certain vaccination' => [
            YesNoUnknown::yes(),
            [
                self::createVaccineInjection(Vaccine::astrazeneca()),
                self::createVaccineInjection(Vaccine::astrazeneca()),
                self::createVaccineInjection(Vaccine::astrazeneca()),
            ],
            [
                new Answer('NCOVvacmerk1', '3'),
                new Answer('NCOVvacmerk2', '3'),
                new Answer('NCOVvacmerk3', '7'),
                new Answer('NCOVafweervac3', 'Onb'),
            ],
        ];

        yield "Injection type other with single line description" => [
            YesNoUnknown::yes(),
            [
                self::createVaccineInjection(Vaccine::other(), "a"),
            ],
            [
                new Answer('NCOVvacmerk1', '7'),
                new Answer('NCOVvacmerk1and', "a"),
            ],
        ];

        yield "Injection type other with multi line description" => [
            YesNoUnknown::yes(),
            [
                self::createVaccineInjection(Vaccine::other(), "a\b"),
            ],
            [
                new Answer('NCOVvacmerk1', '7'),
                new Answer('NCOVvacmerk1and', "a\b"),
            ],
        ];
    }

    /**
     * @param array<VaccineInjection> $injections
     * @param array<Answer> $expectedAnswers
     */
    #[DataProvider('vaccineInjectionsProvider')]
    public function testVaccineInjections(?YesNoUnknown $isVaccinated, array $injections, array $expectedAnswers): void
    {
        $case = $this->createCase($isVaccinated, $injections);
        $this->answersForCase($case)->assertAnswers($expectedAnswers);
    }

    public function testMaxVaccineInjections(): void
    {
        $injections = [];
        for ($i = 0; $i < 10; $i++) {
            $injections[] = self::createVaccineInjection();
        }

        $case = $this->createCase(YesNoUnknown::yes(), $injections);
        $this->answersForCase($case)->assertCount(4 + 2); // only NCOVvacmerkX (4x) + NCOVafweervac3/4
    }

    public function testVaccinationCount(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [
            self::createVaccineInjection(),
            self::createVaccineInjection(),
        ]);

        $this->answersForCase($case)->assertCount(2);
    }

    /**
     * @param array<VaccineInjection>|null $injections
     */
    private function createCase(?YesNoUnknown $isVaccinated = null, ?array $injections = null): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(self::CASE_VERSION)->newInstance();
        assert($case instanceof CovidCaseV3);
        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;
        $case->vaccination->vaccineInjections = $injections;
        return $case;
    }

    private static function createVaccineInjection(
        ?Vaccine $vaccineType = null,
        ?string $otherVaccineType = null,
        ?DateTimeInterface $injectionDate = null,
    ): VaccineInjection {
        $injection = EloquentCase::getSchema()
            ->getVersion(self::CASE_VERSION)
            ->getExpectedField('vaccination')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getExpectedField('vaccineInjections')
            ->newInstance();

        assert($injection instanceof VaccineInjection);

        $injection->injectionDate = $injectionDate;
        $injection->vaccineType = $vaccineType;
        $injection->otherVaccineType = $otherVaccineType;

        return $injection;
    }
}
