<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVvacmerkLtsandBuilder;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVvacmerkLtsandBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVvacmerkLtsandBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testIsNotVaccinated(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::no(),
            vaccine: Vaccine::other(),
        );

        $this->answersForCase($case)->assertCount(0);
    }

    public function testVaccinationCountZero(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), Vaccine::pfizer());
        $case->vaccination->vaccineInjections = [];

        $this->assertCount(0, $case->vaccination->vaccineInjections);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testItSendsCurevacWhenVaccinationInjectionTypeIsCurevac(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), Vaccine::curevac());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLtsand', Vaccine::curevac()->label));
    }

    public function testIsVaccinated(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::other(),
        );

        $this->answersForCase($case)->assertCount(1);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLtsand', 'testVaccine'));
    }

    public function testIsGsk(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: Vaccine::gsk(),
        );

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVvacmerkLtsand', Vaccine::gsk()->label));
        $this->answersForCase($case)->assertCount(1);
    }

    public static function providesCommonVaccines(): array
    {
        return [
            [Vaccine::pfizer()],
            [Vaccine::moderna()],
            [Vaccine::astrazeneca()],
            [Vaccine::janssen()],
            [Vaccine::novavax()],
        ];
    }

    public static function providesUncommonValues(): array
    {
        return [
            [Vaccine::gsk()],
            [Vaccine::curevac()],
        ];
    }

    #[DataProvider('providesUncommonValues')]
    public function testUnCommonVaccineIsSentAsOther(?Vaccine $uncommonVaccine): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: $uncommonVaccine,
        );

        $this->answersForCase($case)->assertCount(1);
    }

    public function testItDoesNotSendAnswerWhenVaccinationTypeIsNull(): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: null,
        );

        $this->answersForCase($case)->assertCount(0);
    }

    #[DataProvider('providesCommonVaccines')]
    public function testCommonVaccineIsNotSentAsOther(Vaccine $commonVaccine): void
    {
        $case = $this->createCase(
            isVaccinated: YesNoUnknown::yes(),
            vaccine: $commonVaccine,
        );

        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(YesNoUnknown $isVaccinated, ?Vaccine $vaccine): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = [
                VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection) use ($vaccine): void {
                    $date = $this->faker->date();
                    $vaccineInjection->vaccineType = $vaccine;
                    $vaccineInjection->injectionDate = new DateTimeImmutable($date);
                    $vaccineInjection->otherVaccineType = 'testVaccine';
                    $vaccineInjection->isInjectionDateEstimated = true;
                }),
            ];
        }

        return $case;
    }
}
