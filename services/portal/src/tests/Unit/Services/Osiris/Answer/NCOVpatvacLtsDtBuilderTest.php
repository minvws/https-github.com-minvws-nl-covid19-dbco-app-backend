<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseCommon;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVpatvacLtsDtBuilder;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\SchemaVersionDataProvider;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;
use TRegx\PhpUnit\DataProviders\DataProvider as TRegxDataProvider;
use Webmozart\Assert\Assert;

#[Builder(NCOVpatvacLtsDtBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVpatvacLtsDtBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('providesVersions')]
    public function testIsNotVaccinated(int $caseVersion, int $vaccineVersion, int $injectionVersion): void
    {
        $date = $this->faker->date();
        $case = $this->createCase(YesNoUnknown::no(), $date, $caseVersion, $vaccineVersion, $injectionVersion);

        $this->answersForCase($case)->assertCount(0);
    }

    #[DataProvider('providesVersions')]
    public function testVaccinationCountZero(int $caseVersion, int $vaccineVersion, int $injectionVersion): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), $this->faker->date(), $caseVersion, $vaccineVersion, $injectionVersion);
        $case->vaccination->vaccineInjections = [];

        $this->assertCount(0, $case->vaccination->vaccineInjections);
        $this->answersForCase($case)->assertEmpty();
    }

    #[DataProvider('providesVersions')]
    public function testIsVaccinated(int $caseVersion, int $vaccineVersion, int $injectionVersion): void
    {
        $date = $this->faker->date();
        $case = $this->createCase(YesNoUnknown::yes(), $date, $caseVersion, $vaccineVersion, $injectionVersion);

        $this->answersForCase($case)->assertAnswer(
            new Answer('NCOVpatvacLtsDt', CarbonImmutable::createFromFormat('Y-m-d', $date)->format('d-m-Y')),
        );
    }

    public static function providesVersions(): iterable
    {
        return TRegxDataProvider::cross(
            SchemaVersionDataProvider::all(EloquentCase::class),
            SchemaVersionDataProvider::all(Vaccination::class),
            SchemaVersionDataProvider::all(VaccineInjection::class),
        );
    }

    private function createCase(YesNoUnknown $isVaccinated, string $date, int $caseVersion, int $vaccineVersion, int $injectionVersion): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        Assert::isInstanceOf($case, CovidCaseCommon::class);

        $case->created_at = CarbonImmutable::now();
        $case->vaccination = Vaccination::newInstanceWithVersion($vaccineVersion);
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = [
                VaccineInjection::newInstanceWithVersion(
                    $injectionVersion,
                    static function (VaccineInjection $vaccineInjection) use ($date): void {
                        $vaccineInjection->vaccineType = Vaccine::novavax();
                        $vaccineInjection->injectionDate = new DateTimeImmutable($date);
                        $vaccineInjection->isInjectionDateEstimated = true;
                    },
                ),
            ];
        }

        return $case;
    }
}
