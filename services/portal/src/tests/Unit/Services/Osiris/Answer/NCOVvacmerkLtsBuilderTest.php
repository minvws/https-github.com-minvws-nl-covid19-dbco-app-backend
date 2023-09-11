<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVvacmerkLtsBuilder;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVvacmerkLtsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVvacmerkLtsBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testIsNotVaccinated(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::no());

        $this->answersForCase($case)->assertCount(0);
    }

    public function testIsVaccinated(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::yes());

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLts', '10'));
    }

    public function testVaccinationCountZero(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::yes());
        $case->vaccination->vaccineInjections = [];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLts', '8'));
    }

    public function testVaccinationCountNull(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::yes());
        $case->vaccination->vaccineInjections = null;

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLts', '8'));
    }

    public function testIsVaccinatedWithGsk(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = YesNoUnknown::yes();
        $case->vaccination->vaccineInjections = [
            VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection): void {
                $date = $this->faker->date();
                $vaccine = Vaccine::gsk();
                $vaccineInjection->vaccineType = $vaccine;
                $vaccineInjection->injectionDate = new DateTimeImmutable($date);
                $vaccineInjection->otherVaccineType = '2';
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
        ];


        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLts', '7'));
    }

    private function createCase(YesNoUnknown $isVaccinated): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = [
                VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection): void {
                    $date = $this->faker->date();
                    $vaccineInjection->vaccineType = Vaccine::novavax();
                    $vaccineInjection->injectionDate = new DateTimeImmutable($date);
                    $vaccineInjection->isInjectionDateEstimated = true;
                }),
            ];
        }

        return $case;
    }
}
