<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwerk2wkBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_unique;
use function assert;
use function count;

#[Builder(NCOVwerk2wkBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwerk2wkBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testJobSectorMappings(): void
    {
        $values = [];

        foreach (JobSector::all() as $sector) {
            $case = $this->createCase(YesNoUnknown::yes(), [$sector]);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid job section mapping for ' . $sector->label);
            $this->assertEquals('NCOVwerk2wk', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $values = array_unique($values);
        $this->assertEquals(count(JobSector::all()), count($values));
    }

    public function testSingleSector(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerk2wk', '1'));
    }

    public function testMultipleSectors(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), [JobSector::ziekenhuis(), JobSector::werkMetDierenOfDierlijkeProducten()]);
        $this->answersForCase($case)->assertAnswers([
            new Answer('NCOVwerk2wk', '1'),
            new Answer('NCOVwerk2wk', '13'),
        ]);
    }

    public function testNoSectors(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), []);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerk2wk', '17'));
    }

    public function testWasAtJobNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerk2wk', '16'));
    }

    public function testWasAtJobUnknown(): void
    {
        $case = $this->createCase(YesNoUnknown::unknown(), [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerk2wk', '17'));
    }

    public function testWasAtJobNull(): void
    {
        $case = $this->createCase(null, [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerk2wk', '17'));
    }

    /**
     * @param array<JobSector> $jobSectors
     */
    private function createCase(?YesNoUnknown $wasAtJob, ?array $jobSectors): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->job->wasAtJob = $wasAtJob;
        $case->job->sectors = $jobSectors;
        return $case;
    }
}
