<?php

namespace Tests\Unit;

use App\Models\ClassificationDetailsAnswer;
use App\Models\ContactDetailsAnswer;
use App\Models\CovidCase;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\SimpleAnswer;
use App\Models\Task;
use App\Repositories\CaseUpdateNotificationRepository;
use App\Repositories\DbAnswerRepository;
use App\Repositories\DbCaseRepository;
use App\Repositories\DbTaskRepository;
use App\Repositories\PairingRepository;
use App\Services\AuthenticationService;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use Jenssegers\Date\Date;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CovidCaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * This test uses a data provider with reflection to make sure we trigger
     * a defect as soon as a new status is added without a matching label
     *
     * @testdox CovidCase provides a label for status $status
     * @dataProvider caseStatusConstantsProvider
     */
    public function testEveryCaseStatusHasALabel(string $status): void
    {
        $this->assertNotEmpty(CovidCase::statusLabel($status));
    }

    public function testInvalidCaseStatusIsHandledGracefully(): void
    {
        $this->assertEquals("Onbekend", CovidCase::statusLabel(null));
        $this->assertEquals("Onbekend", CovidCase::statusLabel(false));
        $this->assertEquals("Onbekend", CovidCase::statusLabel(''));
    }

    /**
     * @testdox Case with status=$status has editable=$editable
     * @dataProvider caseStatusEditableProvider
     */
    public function testCaseStatusDeterminesIfCaseCanBeEdited(string $status, bool $editable): void
    {
        $case = $this->createPartialMock(CovidCase::class, ['caseStatus']);
        $case->method('caseStatus')
            ->willReturn($status);

        $this->assertSame($editable, $case->isEditable());
    }

    /**
     * @testdox Task scenario $_dataName gives progress $expectedProgress
     * @dataProvider caseTaskProgressProvider
     */
    public function testCaseTaskProgress(CovidCase $case, Questionnaire $questionnaire, array $answers, string $expectedProgress): void
    {
        // Setup a testable CovidCase with a task, questionnaire and answers
        // Step 1. the case itself
        $caseRepository = $this->createPartialMock(DbCaseRepository::class, ['getCase']);
        $caseRepository->method('getCase')
            ->willReturn($case);

        // Step 2. add the task to the case
        $taskRepository = $this->createPartialMock(DbTaskRepository::class, ['getTasks']);
        $taskRepository->method('getTasks')
            ->with($case->uuid)
            ->willReturn(collect($case->tasks));

        // Step 3. setup a questionnaire to match the answers against
        $questionnaireService = $this->createPartialMock(QuestionnaireService::class, ['getQuestionnaire']);
        $questionnaireService->method('getQuestionnaire')
            ->with($questionnaire->uuid)
            ->willReturn($questionnaire);

        // Step 4. add the provided answers to this task
        $answerRepository = $this->createPartialMock(DbAnswerRepository::class, ['getAllAnswersByTask']);
        $answerRepository->method('getAllAnswersByTask')
            ->willReturn(collect($answers));

        // Now we can finally wire up the CaseService itself
        $caseService = new CaseService(
            $caseRepository,
            $taskRepository,
            $this->app->make(PairingRepository::class),
            $answerRepository,
            $this->app->make(AuthenticationService::class),
            $this->app->make(CaseUpdateNotificationRepository::class),
            $questionnaireService
        );

        $progressedCase = $caseService->getCase($case->uuid, true);
        $this->assertSame($expectedProgress, $progressedCase->tasks[0]->progress);
    }

    public static function caseTaskProgressProvider(): \Generator
    {
        // Basic questionnaire
        $questionnaire = new Questionnaire;
        $questionnaire->uuid = Uuid::uuid4();
        $questionnaire->taskType = "contact";

        $classificationQuestion = new Question;
        $classificationQuestion->uuid = Uuid::uuid4();
        $classificationQuestion->label = "Classification question";
        $classificationQuestion->questionType = "classificationdetails";
        $classificationQuestion->relevantForCategories = ["1", "2a", "2b", "3"];

        $contactDetailsQuestion = new Question;
        $contactDetailsQuestion->uuid = Uuid::uuid4();
        $contactDetailsQuestion->label = "Contact details question";
        $contactDetailsQuestion->questionType = "contactdetails";
        $contactDetailsQuestion->relevantForCategories = ["1", "2a", "2b", "3"];

        $birthdayQuestion = new Question;
        $birthdayQuestion->uuid = Uuid::uuid4();
        $birthdayQuestion->label = "Birthdate";
        $birthdayQuestion->questionType = "date";
        $birthdayQuestion->relevantForCategories = ["1"];

        $irrelevantQuestion = new Question;
        $irrelevantQuestion->uuid = Uuid::uuid4();
        $irrelevantQuestion->label = "This question is not relevant";
        $irrelevantQuestion->questionType = "date";
        $irrelevantQuestion->relevantForCategories = ["2a"];

        $questionnaire->questions = [
            $classificationQuestion,
            $contactDetailsQuestion,
            $birthdayQuestion,
            $irrelevantQuestion
        ];

        // The last exposure date is part of the Task, not the questionnaire.
        // Task without a last exposure date
        $caseWithoutExposureDate = new CovidCase;
        $caseWithoutExposureDate->uuid = Uuid::uuid4();

        $taskWithoutExposureDate = new Task;
        $taskWithoutExposureDate->uuid = Uuid::uuid4();
        $taskWithoutExposureDate->taskType = "contact";
        $taskWithoutExposureDate->category = "1";
        $taskWithoutExposureDate->caseUuid = $caseWithoutExposureDate->uuid;
        $taskWithoutExposureDate->dateOfLastExposure = null;
        $taskWithoutExposureDate->questionnaireUuid = $questionnaire->uuid;
        $caseWithoutExposureDate->tasks = [$taskWithoutExposureDate];

        yield "no data at all" => [
            $caseWithoutExposureDate,
            $questionnaire,
            [
                // no answers provided by Task
            ],
            Task::TASK_DATA_INCOMPLETE
        ];

        // Task with a last exposure date
        $caseWithExposureDate = new CovidCase;
        $caseWithExposureDate->uuid = Uuid::uuid4();

        $taskWithExposureDate = new Task;
        $taskWithExposureDate->uuid = Uuid::uuid4();
        $taskWithExposureDate->taskType = "contact";
        $taskWithExposureDate->category = "1";
        $taskWithExposureDate->caseUuid = $caseWithExposureDate->uuid;
        $taskWithExposureDate->dateOfLastExposure = new Date();
        $taskWithExposureDate->questionnaireUuid = $questionnaire->uuid;
        $caseWithExposureDate->tasks = [$taskWithExposureDate];

        yield "exposure date, incomplete questionnaire" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                // no answers provided by Task
            ],
            Task::TASK_DATA_INCOMPLETE
        ];

        // Task has answered the basic questions
        $classificationAnswer = new ClassificationDetailsAnswer;
        $classificationAnswer->uuid = Uuid::uuid4();
        $classificationAnswer->taskUuid = $taskWithExposureDate->uuid;
        $classificationAnswer->questionUuid = $classificationQuestion->uuid;
        $classificationAnswer->category1Risk = true;

        yield "exposure date, questionnaire missing contact details" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $classificationAnswer
            ],
            Task::TASK_DATA_INCOMPLETE
        ];

        $contactDetailsAnswer = new ContactDetailsAnswer;
        $contactDetailsAnswer->uuid = Uuid::uuid4();
        $contactDetailsAnswer->taskUuid = $taskWithExposureDate->uuid;
        $contactDetailsAnswer->questionUuid = $contactDetailsQuestion->uuid;
        $contactDetailsAnswer->firstname = "General";
        $contactDetailsAnswer->lastname = "Zod";
        $contactDetailsAnswer->phonenumber = "123456789";
        $contactDetailsAnswer->email = "pl@ceholder";

        yield "exposure date, questionnaire missing classification" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $contactDetailsAnswer
            ],
            Task::TASK_DATA_INCOMPLETE
        ];

        // No exposure date but classication and contact details: not contactable
        yield "no exposure date, basic questionnaire done" => [
            $caseWithoutExposureDate,
            $questionnaire,
            [
                $classificationAnswer,
                $contactDetailsAnswer
            ],
            Task::TASK_DATA_INCOMPLETE
        ];

        // Exposure date, classication and contact details make a Task contactable
        // No more unanswered questions -> progress is complete
        yield "exposure date, basic questionnaire done" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $classificationAnswer,
                $contactDetailsAnswer
            ],
            Task::TASK_DATA_CONTACTABLE
        ];

        yield "exposure date, questionnaire missing one answer" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $classificationAnswer,
                $contactDetailsAnswer,
            ],
            Task::TASK_DATA_CONTACTABLE
        ];

        // Add a blank answer, questionnaire should not be marked complete
        $blankAnswer = new SimpleAnswer;
        $blankAnswer->uuid = Uuid::uuid4();
        $blankAnswer->taskUuid = $taskWithExposureDate->uuid;
        $blankAnswer->questionUuid = $birthdayQuestion->uuid;
        $blankAnswer->value = '';

        yield "exposure date, questionnaire has one blank answer" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $classificationAnswer,
                $contactDetailsAnswer,
                $blankAnswer
            ],
            Task::TASK_DATA_CONTACTABLE
        ];

        // Answering the birthday question completes the survey
        $birthdayAnswer = new SimpleAnswer;
        $birthdayAnswer->uuid = Uuid::uuid4();
        $birthdayAnswer->taskUuid = $taskWithExposureDate->uuid;
        $birthdayAnswer->questionUuid = $birthdayQuestion->uuid;
        $birthdayAnswer->value = new Date();

        yield "exposure date, questionnaire complete" => [
            $caseWithExposureDate,
            $questionnaire,
            [
                $classificationAnswer,
                $contactDetailsAnswer,
                $birthdayAnswer
            ],
            Task::TASK_DATA_COMPLETE
        ];
    }

    public static function caseStatusConstantsProvider(): array
    {
        $caseModel = new \ReflectionClass(CovidCase::class);
        $possibleStatuses = [];

        foreach ($caseModel->getConstants() as $constantName => $constantValue) {
            if (strpos($constantName, 'STATUS_') === 0) {
                $possibleStatuses[] = [$constantValue];
            }
        }

        return $possibleStatuses;
    }

    public static function caseStatusEditableProvider(): array
    {
        $caseModel = new \ReflectionClass(CovidCase::class);
        $possibleStatuses = [];

        // Default: a CovidCase is editable
        foreach ($caseModel->getConstants() as $constantName => $constantValue) {
            $possibleStatuses[$constantValue] = [$constantValue, true];
        }

        // A short list of statuses prevents editing
        $possibleStatuses[CovidCase::STATUS_COMPLETED] = [CovidCase::STATUS_COMPLETED, false];
        $possibleStatuses[CovidCase::STATUS_ARCHIVED] = [CovidCase::STATUS_ARCHIVED, false];
        $possibleStatuses[CovidCase::STATUS_EXPIRED] = [CovidCase::STATUS_EXPIRED, false];

        return $possibleStatuses;
    }
}
