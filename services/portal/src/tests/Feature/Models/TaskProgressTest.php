<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Eloquent\EloquentQuestionnaire;
use App\Services\CaseService;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Metrics\Services\TaskProgressService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\FeatureTestCase;

use function array_merge;

#[Group('task')]
#[Group('task-progress')]
class TaskProgressTest extends FeatureTestCase
{
    #[DataProvider('caseTaskProgressProvider')]
    #[Group('task-progress')]
    #[TestDox('Task progress scenario $_dataName')]
    public function testCaseTaskProgress(
        array $taskData,
        array $questionData,
        array $answerData,
        string $progress,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        /** @var EloquentQuestionnaire $questionnaire */
        $questionnaire = EloquentQuestionnaire::factory()->create();
        $task = $this->createTaskForCase($case, array_merge($taskData, [
            'questionnaire_uuid' => $questionnaire->uuid,
        ]));
        $question = $this->createQuestionForQuestionnaire($questionnaire, array_merge($questionData, [
            'identifier' => 'contactdetails',
            'group_name' => 'contactdetails',
            'question_type' => 'contactdetails',
        ]));
        $this->createAnswerForTaskWithQuestion($task, $question, $answerData);

        $caseService = $this->app->get(CaseService::class);
        $tasks = $caseService->getContactTasks($case->uuid);
        $taskProgressService = $this->app->get(TaskProgressService::class);
        $taskProgress = $taskProgressService->getProgress($tasks[0]->uuid);
        $this->assertSame($progress, $taskProgress);
    }

    public static function caseTaskProgressProvider(): Generator
    {
        yield 'case with incomplete task no classification' => [
            [
                'category' => null,
            ],
            [],
            [],
            TaskProgressService::TASK_DATA_INCOMPLETE,
        ];
        yield 'case with incomplete task no exposure' => [
            [
                'date_of_last_exposure' => null,
            ],
            [],
            [],
            TaskProgressService::TASK_DATA_INCOMPLETE,
        ];
        yield 'case with contactable task' => [
            [
                'category' => ContactCategory::cat2b(),
                'date_of_last_exposure' => CarbonImmutable::now(),
            ],
            [
                'relevant_for_categories' => ContactCategory::cat2b()->value,
            ],
            [
                'ctd_firstname' => 'foo',
                'ctd_phonenumber' => 'bar',
            ],
            TaskProgressService::TASK_DATA_CONTACTABLE,
        ];
        yield 'case with complete task' => [
            [
                'category' => ContactCategory::cat2a(),
                'date_of_last_exposure' => CarbonImmutable::now(),
            ],
            [
                'relevant_for_categories' => ContactCategory::cat2a()->value,
            ],
            [
                'ctd_firstname' => 'foo',
                'ctd_lastname' => 'bar',
                'ctd_email' => 'baz',
                'ctd_phonenumber' => 'qux',
            ],
            TaskProgressService::TASK_DATA_COMPLETE,
        ];
        yield 'case with task without answers' => [
            [
                'category' => ContactCategory::cat2a(),
                'date_of_last_exposure' => CarbonImmutable::now(),
            ],
            [
                'relevant_for_categories' => ContactCategory::cat2a()->value,
            ],
            [],
            TaskProgressService::TASK_DATA_INCOMPLETE,
        ];
        yield 'case with task with blank answers' => [
            [
                'category' => ContactCategory::cat2a(),
                'date_of_last_exposure' => CarbonImmutable::now(),
            ],
            [
                'relevant_for_categories' => ContactCategory::cat2a()->value,
            ],
            [
                'ctd_firstname' => '',
                'ctd_lastname' => '',
                'ctd_email' => '',
                'ctd_phonenumber' => '',
            ],
            TaskProgressService::TASK_DATA_INCOMPLETE,
        ];
    }
}
