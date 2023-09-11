<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV2;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVpatvacV2Builder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVpatvacV2Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVpatvacV2BuilderTest extends TestCase
{
    use AssertAnswers;

    public function testIsVaccinatedWithKnownVaccinationsWithDate(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), 2);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '1'));
    }

    public function testIsVaccinatedWithKnownVaccinationsWithoutDateResultsInKnownCount(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), 1);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '1'));
    }

    public function testIsVaccinatedWithoutKnownVaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '2'));
    }

    public function testIsNotVaccinated(): void
    {
        $case = $this->createCase(YesNoUnknown::no());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '3'));
    }

    public function testIsVaccinatedWithV1Vaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $case->vaccination = Vaccination::newInstanceWithVersion(1, static function (VaccinationV1 $vaccinationV1): void {
            $vaccinationV1->isVaccinated = YesNoUnknown::yes();
        });
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '2'));
    }

    public function testIsVaccinatedWithV2Vaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $case->vaccination = Vaccination::newInstanceWithVersion(2, static function (VaccinationV2 $vaccinationV2): void {
            $vaccinationV2->isVaccinated = YesNoUnknown::yes();
        });
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '2'));
    }

    public function testIsNotVaccinatedWithKnownVaccinationsWithDate(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), 2);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '3'));
    }

    public function testUnknown(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvacV2', '4'));
    }

    private function createCase(?YesNoUnknown $isVaccinated, int $numberOfInjections = 0): EloquentCase
    {
        $case = EloquentCase::getSchema()->getCurrentVersion()->newInstance();
        assert($case instanceof EloquentCase);

        $injections = [];
        $injectionsTestFactory = $case->getSchemaVersion()->getField("vaccination")
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getField("vaccineInjections")
            ->getExpectedType(ArrayType::class)
            ->getExpectedElementType(SchemaType::class)
            ->getSchemaVersion()
            ->getTestFactory();

        for ($i = 0; $i < $numberOfInjections; $i++) {
            $injections[] = $injectionsTestFactory->make();
        }

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;
        $case->vaccination->vaccinationCount = $numberOfInjections;
        $case->vaccination->vaccineInjections = $injections;

        return $case;
    }
}
