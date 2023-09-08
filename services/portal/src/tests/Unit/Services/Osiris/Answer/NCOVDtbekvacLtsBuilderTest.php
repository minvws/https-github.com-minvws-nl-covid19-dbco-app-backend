<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVDtbekvacLtsBuilder;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

/**
 * Is de datum van laatste vaccinatie bekend?
 */
#[Builder(NCOVDtbekvacLtsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVDtbekvacLtsBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testHasKnownLastDate(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::yes());

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVDtbekvacLts', '1'));
    }

    public function testHasUnknownLastDate(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::yes(), lastDateUnknown: true);

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVDtbekvacLts', '2'));
    }

    public function testIsNotVaccinated(): void
    {
        $case = $this->createCase(isVaccinated: YesNoUnknown::no());

        $this->answersForCase($case)->assertCount(0);
    }

    private function createCase(YesNoUnknown $isVaccinated, bool $lastDateUnknown = false): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = $this->createVaccinations($lastDateUnknown);
        }

        return $case;
    }

    private function createVaccinations(bool $lastDateUnknown = false): array
    {
        return [
            VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection) use ($lastDateUnknown): void {
                $vaccineInjection->vaccineType = Vaccine::moderna();
                $vaccineInjection->injectionDate = !$lastDateUnknown ? new DateTimeImmutable($this->faker->date()) : null;
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
        ];
    }
}
