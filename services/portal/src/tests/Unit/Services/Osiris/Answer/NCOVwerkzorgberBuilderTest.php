<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwerkzorgberBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_unique;
use function assert;
use function count;

#[Builder(NCOVwerkzorgberBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwerkzorgberBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testProfessionCareMappings(): void
    {
        $values = [];

        foreach (ProfessionCare::all() as $professionCare) {
            $case = $this->createCase($professionCare, [JobSector::ziekenhuis()]);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid profession care mapping for ' . $professionCare->label);
            $this->assertEquals('NCOVwerkzorgber', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $values = array_unique($values);
        $this->assertEquals(count(ProfessionCare::all()), count($values));
    }

    public function testSingleJobSector(): void
    {
        $case = $this->createCase(ProfessionCare::verpleegkundige(), [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerkzorgber', '2'));
    }

    public function testMultiJobSectors(): void
    {
        $case = $this->createCase(ProfessionCare::verpleegkundige(), [JobSector::politieBrandweer(), JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerkzorgber', '2'));
    }

    public function testProfessionCareNull(): void
    {
        $case = $this->createCase(null, [JobSector::ziekenhuis()]);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testJobSectorsNull(): void
    {
        $case = $this->createCase(ProfessionCare::tandarts(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testJobSectorsEmpty(): void
    {
        $case = $this->createCase(ProfessionCare::tandarts(), []);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testJobSectorNonCare(): void
    {
        $case = $this->createCase(null, [JobSector::politieBrandweer()]);
        $this->answersForCase($case)->assertEmpty();
    }

    /**
     * @param array<JobSector> $jobSectors
     */
    private function createCase(?ProfessionCare $professionCare, ?array $jobSectors): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->job->professionCare = $professionCare;
        $case->job->sectors = $jobSectors;
        return $case;
    }
}
