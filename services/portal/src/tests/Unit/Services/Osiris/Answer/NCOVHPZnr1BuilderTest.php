<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVHPZnr1Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\Group;
use Tests\ModelCreator;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVHPZnr1Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVHPZnr1BuilderTest extends TestCase
{
    use AssertAnswers;
    use DatabaseTransactions;
    use ModelCreator;

    public function testSinglePositiveSource(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::positiveSource(), 'dossier_number' => '1234567']);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVHPZnr1', '1234567'));
    }

    public function testSingleSymptomaticSource(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::symptomaticSource(), 'dossier_number' => '1234567']);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVHPZnr1', '1234567'));
    }

    public function testMultipleSources(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::positiveSource(), 'dossier_number' => '1234567']);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::symptomaticSource(), 'dossier_number' => '1234568']);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testNoDossierNumber(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::positiveSource()]);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testNoSources(): void
    {
        $case = $this->createCase();
        $this->answersForCase($case)->assertEmpty();
    }
}
