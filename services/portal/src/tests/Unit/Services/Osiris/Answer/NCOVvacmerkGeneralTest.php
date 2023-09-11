<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Models\Versions\CovidCase\CovidCaseV4Up;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVDtbekvacLtsBuilder;
use App\Services\Osiris\Answer\NCOVNvacBuilder;
use App\Services\Osiris\Answer\NCOVpatvacV2Builder;
use App\Services\Osiris\Answer\NCOVvacmerkLtsandBuilder;
use App\Services\Osiris\Answer\NCOVvacmerkLtsBuilder;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_merge;
use function assert;

class NCOVvacmerkGeneralTest extends TestCase
{
    use AssertAnswers;

    public function testYesAndDateIsSetItReturnsYes(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::pfizer(),
            injectionDate: new DateTimeImmutable('2022-01-01'),
        );

        $answers = $this->addAnswers(
            builders: [
                NCOVpatvacV2Builder::class,
                NCOVvacmerkLtsandBuilder::class,
                NCOVDtbekvacLtsBuilder::class,
            ],
            case: $case,
        );

        $this->assertAnswers(
            [
                new Answer('NCOVpatvacV2', '1'),
                new Answer('NCOVDtbekvacLts', '1'),
            ],
            $answers,
        );
    }

    public function testYesDateUnknown(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::pfizer(),
            injectionDate: new DateTimeImmutable('2022-01-01'),
        );
        $case->vaccination->vaccineInjections()->last()->injectionDate = null;

        $answers = $this->addAnswers(
            builders: [
                NCOVpatvacV2Builder::class,
                NCOVvacmerkLtsandBuilder::class,
                NCOVDtbekvacLtsBuilder::class,
            ],
            case: $case,
        );

        $this->assertAnswers(
            [
                new Answer('NCOVpatvacV2', '1'),
                new Answer('NCOVDtbekvacLts', '2'),
            ],
            $answers,
        );
    }

    public function testNCOVNvacNullAndNCOVvacmerkLtsandNullWhenVaccinatedTrueButInjectionsNull(): void
    {
        $case = EloquentCase::getSchema()->getCurrentVersion()->newInstance();
        assert($case instanceof CovidCaseV4Up);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = YesNoUnknown::yes();
        $case->vaccination->vaccinationCount = null;
        $case->vaccination->vaccineInjections = [];

        $answers = $this->addAnswers(
            builders: [
                NCOVNvacBuilder::class,
                NCOVvacmerkLtsandBuilder::class,
            ],
            case: $case,
        );

        $this->assertEmpty($answers);
    }

    public function testAmountOfVaccinationsDoesNotSendNCOVvacmerkLtsand(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::pfizer(),
            injectionDate: new DateTimeImmutable('2022-01-01'),
        );


        $answers = $this->addAnswers(
            builders: [
                NCOVNvacBuilder::class,
                NCOVvacmerkLtsandBuilder::class,
            ],
            case: $case,
        );

        $this->assertAnswers(
            [
                new Answer('NCOVNvac', (string) $case->vaccination->vaccinationCount()),
            ],
            $answers,
        );
    }

    public function testItSendsNCOVDtbekvacLtsWhenVaccinatedAndDateKnown(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::pfizer(),
            injectionDate: null,
        );

        $answers = $this->addAnswers(
            builders: [
                NCOVpatvacV2Builder::class,
                NCOVDtbekvacLtsBuilder::class,
                NCOVvacmerkLtsandBuilder::class,
            ],
            case: $case,
        );

        $this->assertAnswers(
            [
                new Answer('NCOVpatvacV2', '1'),
                new Answer('NCOVDtbekvacLts', '2'),
            ],
            $answers,
        );
    }

    public function testItDoesNotThrowErrorsWhenNCOVNvacAlsoIncluded(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::pfizer(),
            injectionDate: null,
        );

        $answers = $this->addAnswers(
            builders: [
                NCOVpatvacV2Builder::class,
                NCOVDtbekvacLtsBuilder::class,
                NCOVvacmerkLtsandBuilder::class,
                NCOVNvacBuilder::class,
            ],
            case: $case,
        );


        $this->assertAnswers(
            [
                new Answer('NCOVpatvacV2', '1'),
                new Answer('NCOVDtbekvacLts', '2'),
                new Answer('NCOVNvac', '1'),
            ],
            $answers,
        );
    }

    public function testItDoesNotThrowExceptionWhenNCOVvacmerkLtsIsUnknown(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: null,
            injectionDate: null,
        );

        $answers = $this->addAnswers(
            builders: [
                NCOVvacmerkLtsBuilder::class,
                NCOVvacmerkLtsandBuilder::class,
            ],
            case: $case,
        );

        $this->assertAnswers(
            [
                new Answer('NCOVvacmerkLts', '8'),
            ],
            $answers,
        );
    }

    /**
     * @return array<Answer>
     */
    private function addAnswers(array $builders, EloquentCase $case): array
    {
        $answers = [];
        foreach ($builders as $builderClass) {
            $builder = new $builderClass();
            $answersForBuilder = $builder->build($case);
            $answers = array_merge($answers, $answersForBuilder);
        }
        return $answers;
    }

    private function createCase(
        YesNoUnknown $isVaccinated,
        ?Vaccine $vaccine,
        ?DateTimeImmutable $injectionDate,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = [
                VaccineInjection::newInstanceWithVersion(
                    1,
                    static function (VaccineInjection $vaccineInjection) use ($vaccine, $injectionDate): void {
                        $vaccineInjection->vaccineType = $vaccine;
                        $vaccineInjection->injectionDate = $injectionDate;
                        $vaccineInjection->otherVaccineType = '2';
                        $vaccineInjection->isInjectionDateEstimated = true;
                    },
                ),
            ];
        }

        return $case;
    }
}
