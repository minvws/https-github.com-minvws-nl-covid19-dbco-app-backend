<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVNContactCat2Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\Group;
use Tests\ModelCreator;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVNContactCat2Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVNContactCat2BuilderTest extends TestCase
{
    use AssertAnswers;
    use DatabaseTransactions;
    use ModelCreator;

    public function testSingleCat2aContact(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2a()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '1'));
    }

    public function testSingleCat2bContact(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2b()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '1'));
    }

    public function testMultiCat2Contacts(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2a()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2b()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2a()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '3'));
    }

    public function testSingleCat2ContactWithinMultipleContacts(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat1()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat2b()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat1()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '1'));
    }

    public function testNoCat2Contacts(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat1()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::contact(), 'category' => ContactCategory::cat1()]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '0'));
    }

    public function testNoContacts(): void
    {
        $case = $this->createCase();
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVNContactCat2', '0'));
    }
}
