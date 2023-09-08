<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Requests\PseudoBsnUpdateRequest;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task;
use App\Services\CaseConnectedService;
use App\Services\Task\TaskDecryptableDefiner;
use App\Services\Task\TaskEncoder;
use App\Services\TaskService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use LogicException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\ValueTypeMismatchException;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Metrics\Services\TaskProgressService;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_map;
use function response;
use function sprintf;

class ApiTaskController extends ApiController
{
    use ValidatesModels;

    private CaseConnectedService $caseConnectedService;
    private TaskService $taskService;
    private TaskProgressService $taskProgressService;
    private TaskEncoder $taskEncoder;
    private TaskDecryptableDefiner $taskDecryptableDefiner;

    public function __construct(
        CaseConnectedService $caseConnectedService,
        TaskService $taskService,
        TaskProgressService $taskProgressService,
        TaskDecryptableDefiner $taskDecryptableDefiner,
    ) {
        $this->caseConnectedService = $caseConnectedService;
        $this->taskService = $taskService;
        $this->taskProgressService = $taskProgressService;
        $this->taskEncoder = new TaskEncoder($taskDecryptableDefiner);
        $this->taskDecryptableDefiner = $taskDecryptableDefiner;
    }

    #[SetAuditEventDescription('Case contacten opgehaald')]
    public function getCaseTasks(EloquentCase $case, TaskGroup $group, Request $request, AuditEvent $auditEvent): JsonResponse
    {
        $includeProgress = $request->input('includeProgress', false);

        $tasks = $this->taskService->getTasks($case->uuid, $group);

        $auditEvent->objects(
            AuditObject::createArray(
                $tasks,
                static fn (Task $t) => AuditObject::create('task', $t->uuid)
            ),
        );

        if ($includeProgress) {
            $this->applyProgress($tasks);
        }

        return response()->json(['tasks' => array_map(fn($task) => $this->taskEncoder->encode($task), $tasks)]);
    }

    /**
     * @throws ValidationException
     */
    #[SetAuditEventDescription('Case contacten bijgewerkt')]
    public function updateTask(Request $request, EloquentTask $eloquentTask): JsonResponse
    {
        $task = $this->taskService->getTask($eloquentTask->uuid);
        Assert::notNull($task);

        // Set input data
        $postData = $request->json()->all();

        // Validate the request data
        $validationResult = $this->validateModel(Task::class, [
            'uuid' => $postData['task']['uuid'] ?? $task->uuid,
            'caseUuid' => $postData['task']['caseUuid'] ?? $task->caseUuid,
            'group' => $postData['task']['group'] ?? $task->group?->value,
            'label' => $postData['task']['label'] ?? $task->label,
            'taskContext' => $postData['task']['taskContext'] ?? $task->taskContext,
            'nature' => $postData['task']['nature'] ?? $task->nature,
            'category' => $postData['task']['category'] ?? $task->category,
            'communication' => $postData['task']['communication'] ?? $task->communication,
            'informedByStaffAt' => $postData['task']['informedByStaffAt'] ?? $task->informedByStaffAt,
            'isSource' => $postData['task']['isSource'] ?? $task->isSource,
            'informStatus' => $postData['task']['informStatus'] ?? null,
            'dateOfLastExposure' => $postData['task']['dateOfLastExposure'] ?? null,
        ]);
        if (isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            return new JsonResponse(['validationResult' => $validationResult], 422);
        }

        if (isset($postData['task']['label'])) {
            $task->label = $postData['task']['label'];
        }
        if (array_key_exists('task', $postData) && array_key_exists('group', $postData['task'])) {
            $task->group = TaskGroup::tryFrom($postData['task']['group']);
        }
        $task->taskContext = $postData['task']['taskContext'] ?? null;
        $task->nature = $postData['task']['nature'] ?? null;
        $task->category = $postData['task']['category'] ?? null;
        $task->informedByStaffAt = isset($postData['task']['informedByStaffAt'])
            ? CarbonImmutable::parse($postData['task']['informedByStaffAt'])
            : null;
        $task->dateOfLastExposure = isset($postData['task']['dateOfLastExposure'])
            ? CarbonImmutable::parse($postData['task']['dateOfLastExposure'])
            : null;

        if (isset($postData['task']['communication'])) {
            $task->communication = $postData['task']['communication'];
        } elseif ($task->communication === null && isset($postData['task']['category'])) {
            $task->communication = $this->defineCommunicationByCategory($postData['task']['category']);
        }

        $task->isSource = $postData['task']['isSource'] ?? false;
        if (isset($postData['task']['informStatus'])) {
            $task->informStatus = InformStatus::from($postData['task']['informStatus']);
        }
        $this->taskService->updateTask($task);

        // When there are warnings, add them to the response
        if (isset($validationResult[Validatable::SEVERITY_LEVEL_WARNING])) {
            return response()->json([
                'task' => $this->taskEncoder->encode($task),
                'validationResult' => $this->prefixDottedFormat($validationResult, 'task'),
            ]);
        }

        // Return the task without validationResult
        return response()->json(['task' => $this->taskEncoder->encode($task)]);
    }

    /**
     * @throws ValueTypeMismatchException
     * @throws ValidationException
     */
    #[SetAuditEventDescription('Case contact aangemaakt')]
    public function createTask(Request $request, EloquentCase $case, AuditEvent $auditEvent): JsonResponse
    {
        $auditObject = AuditObject::create('task');
        $auditEvent->object($auditObject);

        // Set input data
        $postData = $request->json()->all();

        // Ensures the right validation will be triggered
        $postData['task']['uuid'] = null;
        $postData['task']['caseUuid'] = $case->uuid;

        // Validate the request data
        $validationResult = $this->validateModel(Task::class, $postData['task']);
        if (isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            return new JsonResponse([
                'validationResult' => $this->prefixDottedFormat($validationResult, 'task'),
            ], 422);
        }

        // Pick a default way of getting in touch with the Task
        if (isset($postData['task']['category']) && !isset($postData['task']['communication'])) {
            $postData['task']['communication'] = $this->defineCommunicationByCategory($postData['task']['category']);
        }

        // Create the task
        $newTask = $this->taskService->createTask(
            $case->uuid,
            $postData['task']['group'] ? TaskGroup::from($postData['task']['group']) : TaskGroup::contact(),
            $postData['task']['label'],
            $postData['task']['taskContext'] ?? null,
            $postData['task']['nature'] ?? null,
            $postData['task']['category'] ?? null,
            $postData['task']['communication'] ?? null,
            isset($postData['task']['dateOfLastExposure']) ? CarbonImmutable::parse($postData['task']['dateOfLastExposure']) : null,
            $postData['task']['isSource'] ?? false,
        );

        $auditObject->identifier($newTask->uuid);

        // When there are warnings, add them to the response
        if (isset($validationResult[Validatable::SEVERITY_LEVEL_WARNING])) {
            return response()->json([
                'task' => (new Encoder())->encode($newTask),
                'validationResult' => $this->prefixDottedFormat($validationResult, 'task'),
            ]);
        }

        // Return the task without validationResult
        return response()->json(['task' => (new Encoder())->encode($newTask)]);
    }

    #[SetAuditEventDescription('Case contact verwijderd')]
    public function deleteTask(EloquentTask $eloquentTask): JsonResponse
    {
        $this->taskService->deleteTask($eloquentTask);

        return response()->json(['task' => $eloquentTask]);
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Pseudo BSN bijgewerkt')]
    public function updatePseudoBsn(EloquentTask $eloquentTask, PseudoBsnUpdateRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $bsnAuditObject = AuditObject::create('pseudo-bsn');
        $bsnAuditObject->identifier($eloquentTask->pseudoBsnGuid ?? '');
        $bsnAuditObject->detail('newPseudoBsnGuid', $request->getPseudoBsnGuid());
        $auditEvent->object($bsnAuditObject);

        $task = $this->taskService->getTask($eloquentTask->uuid);
        Assert::notNull($task);

        $this->taskService->updatePseudoBsn($task, $request->getPseudoBsnGuid());

        return response()->json(['task' => $this->taskService->getTask($task->uuid)]);
    }

    /**
     * Task completion progress is divided into three buckets to keep the UI simple:
     * - 'completed': all details are available, all questions answered
     * - 'contactable': we have enough basic data to contact the person
     * - 'incomplete': too much is still missing, provide the user UI warnings
     *
     * @param array<Task> $tasks
     */
    private function applyProgress(array $tasks): void
    {
        foreach ($tasks as &$task) {
            if ($this->taskDecryptableDefiner->isDecryptable($task)) {
                $task->progress = $this->taskProgressService->getProgress($task->uuid);
            }
        }
    }

    private function defineCommunicationByCategory(string $category): string
    {
        switch ($category) {
            case ContactCategory::cat1()->value:
            case ContactCategory::cat2a()->value:
            case ContactCategory::cat2b()->value:
                return InformedBy::staff()->value;
            case '3': // deprecated
            case ContactCategory::cat3a()->value:
            case ContactCategory::cat3b()->value:
                return InformedBy::index()->value;
        }

        throw new LogicException(sprintf('Unexpected given category %s', $category));
    }

    #[SetAuditEventDescription('Verbonden contacten opgehaald')]
    public function getConnectedTasks(EloquentTask $eloquentTask, AuditEvent $auditEvent): JsonResponse
    {
        $taskAuditObject = AuditObject::create('task', $eloquentTask->uuid);
        $auditEvent->object($taskAuditObject);

        if ($eloquentTask->pseudo_bsn_guid === null) {
            return response()->json(['error' => 'Task has no PseudoBSN'], Response::HTTP_BAD_REQUEST);
        }

        $connectedCasesAndTasks = $this->caseConnectedService->getConnectedCasesAndTasksForTask($eloquentTask);
        $taskAuditObject->detail('connectedCases', array_map(static fn ($c) => $c['uuid'], $connectedCasesAndTasks['cases']->toArray()));
        $taskAuditObject->detail('connectedTasks', array_map(static fn ($c) => $c['uuid'], $connectedCasesAndTasks['tasks']->toArray()));

        return response()->json($connectedCasesAndTasks);
    }
}
