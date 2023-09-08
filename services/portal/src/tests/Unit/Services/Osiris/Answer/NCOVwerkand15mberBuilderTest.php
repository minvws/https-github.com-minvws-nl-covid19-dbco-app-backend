<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwerkand15mberBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_unique;
use function assert;
use function count;

#[Builder(NCOVwerkand15mberBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwerkand15mberBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testProfessionOtherMappings(): void
    {
        $values = [];

        foreach (ProfessionOther::all() as $professionOther) {
            $case = $this->createCase(YesNoUnknown::yes(), $professionOther);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid profession other mapping for ' . $professionOther->label);
            $this->assertEquals('NCOVwerkand15mber', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $values = array_unique($values);
        $this->assertEquals(count(ProfessionOther::all()), count($values));
    }

    public function testCloseContactAtJobYes(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), ProfessionOther::rijinstructeur());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerkand15mber', '5'));
    }

    public function testCloseContactAtJobNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), ProfessionOther::rijinstructeur());
        $this->answersForCase($case)->assertEmpty();
    }

    public function testProfessionOtherNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $closeContactAtJob, ?ProfessionOther $professionOther): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->job->closeContactAtJob = $closeContactAtJob;
        $case->job->professionOther = $professionOther;
        return $case;
    }
}
