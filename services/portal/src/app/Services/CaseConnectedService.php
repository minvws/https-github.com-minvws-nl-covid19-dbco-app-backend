<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FragmentNotAccessibleException;
use App\Models\CovidCase\General;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Repositories\CaseRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function collect;

/**
 * Responsible for managing connected cases and tasks.
 *
 * @phpstan-type ConnectedCaseArray array{
 *   uuid: string,
 *   organisation: non-empty-array,
 *   number: string,
 *   dateOfSymptomOnset: ?string,
 *   hasSymptoms: bool,
 *   dateOfTest: ?string
 * }
 */
class CaseConnectedService
{
    private CaseFragmentService $caseFragmentService;
    private CaseRepository $caseRepository;
    private TaskFragmentService $taskFragmentService;
    private TaskRepository $taskRepository;
    private AuthenticationService $authService;

    public function __construct(
        CaseFragmentService $caseFragmentService,
        CaseRepository $caseRepository,
        TaskFragmentService $taskFragmentService,
        TaskRepository $taskRepository,
        AuthenticationService $authService,
    ) {
        $this->caseFragmentService = $caseFragmentService;
        $this->caseRepository = $caseRepository;
        $this->taskFragmentService = $taskFragmentService;
        $this->taskRepository = $taskRepository;
        $this->authService = $authService;
    }

    public function getConnectedCasesAndTasksForCase(EloquentCase $case): array
    {
        return $this->getConnectedCasesAndTasksForPseudoBsnGuid($case->pseudo_bsn_guid, $case, null);
    }

    public function getConnectedCasesAndTasksForTask(EloquentTask $task): array
    {
        return $this->getConnectedCasesAndTasksForPseudoBsnGuid($task->pseudo_bsn_guid, null, $task);
    }

    private function getConnectedCasesAndTasksForPseudoBsnGuid(
        ?string $pseudoBsnGuid,
        ?EloquentCase $case,
        ?EloquentTask $task,
    ): array {
        if ($pseudoBsnGuid === null) {
            return [
                'cases' => collect(),
                'tasks' => collect(),
            ];
        }

        $ignoreCaseUuids = [];
        $ignoreTaskUuids = [];

        if ($case !== null) {
            $ignoreCaseUuids[] = $case->uuid;
            $ignoreTaskUuids = $case->tasks()->pluck('uuid')->toArray();
        }

        if ($task !== null) {
            $ignoreCaseUuids[] = $task->covidCase->uuid;
            $ignoreTaskUuids[] = $task->uuid;
        }

        $cases = $this->caseRepository->getCasesByPseudoBsnGuid($pseudoBsnGuid, $ignoreCaseUuids);
        $tasks = $this->taskRepository->getTasksByPseudoBsnGuid($pseudoBsnGuid, $ignoreTaskUuids);

        return [
            'cases' => $this->convertConnectedCases($cases),
            'tasks' => $this->convertConnectedTasks($tasks),
        ];
    }

    /**
     * @param Collection<array-key,EloquentCase> $cases
     *
     * @return Collection<int,ConnectedCaseArray>
     */
    private function convertConnectedCases(Collection $cases): Collection
    {
        return $cases->map(function (EloquentCase $case): array {
            $fragments = $this->caseFragmentService->loadFragments($case->uuid, ['general', 'test', 'symptoms'], false, true);

            /** @var General $general */
            $general = $fragments['general'];
            /** @var Test $test */
            $test = $fragments['test'];
            /** @var Symptoms $symptoms */
            $symptoms = $fragments['symptoms'];

            if ($case->organisation !== null) {
                $organisation = $case->organisation->only(['uuid', 'abbreviation']);
                $organisation['isCurrent'] = $this->authService->getRequiredSelectedOrganisation()->uuid === $organisation['uuid'];
            } else {
                $organisation['isCurrent'] = false;
            }

            return [
                'uuid' => $case->uuid,
                'organisation' => $organisation,
                'number' => $general->reference,
                'dateOfSymptomOnset' => $test->dateOfSymptomOnset ? $test->dateOfSymptomOnset->format('Y-m-d') : null,
                'hasSymptoms' => $symptoms->hasSymptoms === YesNoUnknown::yes(),
                'dateOfTest' => $test->dateOfTest ? $test->dateOfTest->format('Y-m-d') : null,
            ];
        });
    }

    private function convertConnectedTasks(Collection $tasks): Collection
    {
        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: this code does way to much Eloquent stuff inside the service
        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: this code doesn't follow our naming scheme (no camel case)
        //@phpstan-ignore-next-line Terrible code, but it works
        return $tasks->map(function (EloquentTask $task) {
            // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
            // TODO: as soon as the general task fragment uses versioning just use $task->general
            try {
                $task_fragments = $this->taskFragmentService->loadFragments($task->uuid, ['general']);
            } catch (FragmentNotAccessibleException $fragmentNotAccessibleException) {
                return false;
            }

            /** @var \App\Models\Task\General $task_general */
            $task_general = $task_fragments['general'];
            /** @var General $case_general */
            $case_general = $task->covidCase->general;

            if ($task->covidCase->organisation !== null) {
                $organisation = $task->covidCase->organisation->only(['uuid', 'abbreviation']);
                $organisation['isCurrent'] = $this->authService->getRequiredSelectedOrganisation()->uuid === $organisation['uuid'];
            } else {
                $organisation['isCurrent'] = false;
            }

            return [
                'uuid' => $task->uuid,
                'organisation' => $organisation,
                'number' => $case_general->reference,
                'category' => $task_general->category ? $task_general->category->value : null,
                'dateOfLastExposure' => $task_general->dateOfLastExposure ? $task_general->dateOfLastExposure->format('Y-m-d') : null,
                'relationship' => $task_general->relationship ? $task_general->relationship->value : $task_general->otherRelationship,
            ];
        })->reject(static function ($value) {
            return $value === false;
        });
    }
}
