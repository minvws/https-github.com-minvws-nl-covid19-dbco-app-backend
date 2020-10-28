<?php

namespace App\Http\Controllers;

use App\Models\CovidCase;
use App\Models\Task;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use Illuminate\Http\Request;
use Jenssegers\Date\Date;


class CaseController extends Controller
{
    private CaseService $caseService;
    private QuestionnaireService $questionnaireService;

    public function __construct(CaseService $caseService,
                                QuestionnaireService $questionnaireService)
    {
        $this->caseService = $caseService;
        $this->questionnaireService = $questionnaireService;
    }

    public function newCase()
    {
        // Because we want to show the new case immediately, we create a draft case.
        $case = $this->caseService->createDraftCase();

        return redirect()->intended('/newcaseedit/' . $case->uuid);
    }

    public function draftCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case != null && $this->caseService->canAccess($case)) {
            $case->tasks[] = new Task(); // one empty placeholder
            return view('draftcase', ['case' => $case, 'tasks' => $case->tasks]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function editCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case != null && $this->caseService->canAccess($case)) {

            $taskgroups = array();
            foreach ($case->tasks as $task) {
                $taskgroups[$task->communication][] = $task;
            }

            return view('editcase', ['case' => $case, 'taskgroups' => $taskgroups]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function dumpCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case != null && $this->caseService->canAccess($case)) {
            $tasks = $this->questionnaireService->getRobotFriendlyTaskExport($caseUuid);
            return view('dumpcase', [ 'case' => $case, 'headers' => $tasks['headers'], 'taskcategories' => $tasks['categories'] ]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function listCases()
    {
        $cases = $this->caseService->myCases();

        // Enrich data with some view level helper data
        foreach ($cases as $case) {
            $case->editCommand = ($case->status == CovidCase::STATUS_DRAFT ? 'newcaseedit' : 'case');
        }

        return view('caseoverview', ['cases' => $cases]);
    }

    public function saveCase(Request $request)
    {
        $caseUuid = $request->input('caseUuid');

        $case = $this->caseService->getCase($caseUuid);

        if ($case != null && $this->caseService->canAccess($case)) {

            $validatedData = $request->validate([
                'name' => 'required'
            ]);

            $case->name = $validatedData['name'];

            $case->caseId = $request->input('caseId');
            $case->dateOfSymptomOnset = Date::parse($request->input('dateOfSymptomOnset'));
            $case->status = 'open'; // TODO: only set to open once a pairing code was assigned

            $this->caseService->updateCase($case);

            foreach ($request->input('tasks') as $rawTask) {
                if (!empty($rawTask['label'])) { // skip empty auto-added table rows
                    $this->caseService->createOrUpdateTask($caseUuid, $rawTask);
                }
            }
        }

        return redirect()->intended('/paircase/' . $caseUuid);

    }

    /**
     * Start pairing process.
     *
     * @param $caseUuid

     */
    public function pairCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case != null && $this->caseService->canAccess($case)) {

            $pairingCode = $this->caseService->createPairingCodeForCase($caseUuid);

            // When we show the pairingcode, we mark the case as 'open'.
            $this->caseService->openCase($case);

            return view('paircase', ['case' => $case, 'pairingCode' => $pairingCode]);
        }
        return redirect()->intended('/');
    }
}
