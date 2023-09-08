<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVTypeContact1Builder;
use Generator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\ModelCreator;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVTypeContact1Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVTypeContact1BuilderTest extends TestCase
{
    use AssertAnswers;
    use DatabaseTransactions;
    use ModelCreator;

    public static function sourceCategoryProvider(): Generator
    {
        yield [ContactCategory::cat1(), '1'];
        yield [ContactCategory::cat2a(), '2'];
        yield [ContactCategory::cat2b(), '2'];
        yield [ContactCategory::cat3a(), '3'];
        yield [ContactCategory::cat3b(), '3'];
    }

    #[DataProvider('sourceCategoryProvider')]
    public function testSinglePositiveSource(ContactCategory $category, string $expectedValue): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::positiveSource(), 'category' => $category]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVTypeContact1', $expectedValue));
    }

    #[DataProvider('sourceCategoryProvider')]
    public function testSingleSymptomaticSource(ContactCategory $category, string $expectedValue): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::symptomaticSource(), 'category' => $category]);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVTypeContact1', $expectedValue));
    }

    public function testMultipleSources(): void
    {
        $case = $this->createCase();
        $this->createTaskForCase($case, ['task_group' => TaskGroup::positiveSource(), 'category' => ContactCategory::cat1()]);
        $this->createTaskForCase($case, ['task_group' => TaskGroup::symptomaticSource(), 'category' => ContactCategory::cat2a()]);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testNoSources(): void
    {
        $case = $this->createCase();
        $this->answersForCase($case)->assertEmpty();
    }
}
