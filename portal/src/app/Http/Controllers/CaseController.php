<?php

namespace App\Http\Controllers;

use App\Models\CovidCase;
use App\Models\Task;
use App\Repositories\CaseRepository;
use App\Repositories\TaskRepository;
use Illuminate\Http\Request;
use Jenssegers\Date\Date;


class CaseController extends Controller
{
    private CaseRepository $caseRepository;
    private TaskRepository $taskRepository;

    public function __construct(CaseRepository $caseRepository, TaskRepository $taskRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->taskRepository = $taskRepository;
    }

    public function newCase()
    {
        // Because we want to show the new case immediately, we create a draft case.
        $case = $this->caseRepository->createDraftCase();

        return redirect()->intended('/newcaseedit/'.$case->uuid);
    }

    public function draftCase($caseUuid)
    {
        $case = $this->caseRepository->getCase($caseUuid);

        if ($case != null && $this->verifyCaseAccess($case)) {
            $tasks = $this->taskRepository->getTasks($caseUuid);
            $tasks[] = new Task(); // one empty placeholder
            return view('draftcase', ['case' => $case, 'tasks' => $tasks]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function editCase($caseUuid)
    {
        $case = $this->caseRepository->getCase($caseUuid);

        if ($case != null && $this->verifyCaseAccess($case)) {
            $tasks = $this->taskRepository->getTasks($caseUuid);

            $taskgroups = array();
            foreach ($tasks as $task) {
                $taskgroups[$task->communication][] = $task;
            }

            return view('editcase', [ 'case' => $case, 'taskgroups' => $taskgroups ]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function listCases()
    {
        $cases = $this->caseRepository->myCases();

        // Enrich dat with some view level helper data
        foreach ($cases as $case) {
            $case->editCommand = ($case->status == CovidCase::STATUS_DRAFT ? 'newcaseedit' : 'case');
        }

        return view('caseoverview', ['cases' => $cases]);
    }

    public function saveCase(Request $request)
    {
        $uuid = $request->input('uuid');

        $case = $this->caseRepository->getCase($uuid);

        if ($case != null && $this->verifyCaseAccess($case)) {

            $case->name = $request->input('name');
            $case->caseId = $request->input('caseId');
            $case->dateOfSymptomOnset = Date::parse($request->input('dateOfSymptomOnset'));
            $case->status = 'open'; // TODO: only set to open once a pairing code was assigned

            $this->caseRepository->updateCase($case);

            foreach ($request->input('tasks') as $rawTask) {
                if (!empty($rawTask['label'])) { // skip empty auto-added table rows
                    if (isset($rawTask['uuid'])) {
                        $task = $this->taskRepository->getTask($rawTask['uuid']);
                        $task->label = $rawTask['label'];
                        $task->taskContext = $rawTask['context'];
                        $task->category = $rawTask['category'];
                        $task->dateOfLastExposure = Date::parse($rawTask['dateOfLastExposure']);
                        $task->communication = $rawTask['communication'];
                        $this->taskRepository->updateTask($task);
                    } else {
                        $this->taskRepository->createTask($case->uuid,
                            $rawTask['label'],
                            $rawTask['context'],
                            $rawTask['category'],
                            Date::parse($rawTask['dateOfLastExposure']),
                            $rawTask['communication']);

                    }
                }
            }
        }

        return redirect()->intended('/');

    }

    /**
     * Check if the current user has access to a case
     * @param $case The case to check
     * @return bool True if access is ok
     */
    private function verifyCaseAccess($case)
    {
        // Todo: in the future we might want to have people edit each other's cases
        return $this->caseRepository->isOwner($case);
    }
}
