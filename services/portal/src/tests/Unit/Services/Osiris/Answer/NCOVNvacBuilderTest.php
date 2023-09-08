<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV4Up;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVNvacBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVNvacBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVNvacBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testSingleVaccination(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), 1);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNvac', '1'));
    }

    public function testMultipleVaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), 3);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNvac', '3'));
    }

    public function testZeroVaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), 0);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testNoVaccinations(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), 0);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $isVaccinated = null, int $numberOfInjections = 0): EloquentCase
    {
        $case = EloquentCase::getSchema()->getCurrentVersion()->newInstance();
        assert($case instanceof CovidCaseV4Up);

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
