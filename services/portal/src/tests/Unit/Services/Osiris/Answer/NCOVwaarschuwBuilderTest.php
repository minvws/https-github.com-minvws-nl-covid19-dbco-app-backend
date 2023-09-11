<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Test\TestV2;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwaarschuwBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\TestReason;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVwaarschuwBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwaarschuwBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testSingleIsWarnedReason(): void
    {
        $case = $this->createCase([TestReason::contact()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'J'));
    }

    public function testMultipleReasonsSingleIsWarned(): void
    {
        $case = $this->createCase([TestReason::medicalTreatment(), TestReason::contact()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'J'));
    }

    public function testMultipleIsWarnedReasons(): void
    {
        $case = $this->createCase([TestReason::contactWarnedByGgd(), TestReason::contact()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'J'));
    }

    public function testSingleNotWarnedReason(): void
    {
        $case = $this->createCase([TestReason::medicalTreatment()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'N'));
    }

    public function testMultipleNotWarnedReasons(): void
    {
        $case = $this->createCase([TestReason::medicalTreatment(), TestReason::symptoms()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'N'));
    }

    public function testReasonsEmpty(): void
    {
        $case = $this->createCase([]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'Onb'));
    }

    public function testReasonsNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwaarschuw', 'Onb'));
    }

    /**
     * @param array<TestReason> $testReasons
     */
    private function createCase(?array $testReasons): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        assert(isset($case->test));
        assert($case->test instanceof TestV2);
        $case->test->reasons = $testReasons;
        return $case;
    }
}
