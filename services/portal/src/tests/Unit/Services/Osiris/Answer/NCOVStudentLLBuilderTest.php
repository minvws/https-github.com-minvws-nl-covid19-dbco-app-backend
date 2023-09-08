<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVStudentLLBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\EduDaycareType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_unique;
use function assert;
use function count;

#[Builder(NCOVStudentLLBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVStudentLLBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testEduDayCareTypeMappings(): void
    {
        $values = [];

        foreach (EduDaycareType::all() as $type) {
            $case = $this->createCase(YesNoUnknown::yes(), $type);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1);
            $this->assertEquals('NCOVStudentLL', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $values = array_unique($values);

        $this->assertEquals(count(EduDaycareType::all()), count($values));
    }

    public function testTypePrimaryEducation(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), EduDaycareType::primaryEducation());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVStudentLL', '3'));
    }

    public function testTypeNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVStudentLL', '5')); // unknown
    }

    public function testIsStudentNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), EduDaycareType::primaryEducation());
        $this->answersForCase($case)->assertEmpty();
    }

    public function testForEmptyCaseV5(): void
    {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(YesNoUnknown $isStudent, ?EduDaycareType $type): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->eduDaycare->isStudent = $isStudent;
        $case->eduDaycare->type = $type;
        return $case;
    }
}
