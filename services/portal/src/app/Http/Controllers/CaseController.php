<?php

namespace App\Http\Controllers;

use App\Models\CovidCase;
use App\Models\Task;
use App\Services\AuthenticationService;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use App\Services\TaskService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Jenssegers\Date\Date;

class CaseController extends Controller
{
    private CaseService $caseService;
    private UserService $userService;
    private TaskService $taskService;
    private QuestionnaireService $questionnaireService;
    private AuthenticationService $authService;

    public function __construct(CaseService $caseService,
                                UserService $userService,
                                TaskService $taskService,
                                QuestionnaireService $questionnaireService,
                                AuthenticationService $authService)
    {
        $this->caseService = $caseService;
        $this->userService = $userService;
        $this->taskService = $taskService;
        $this->questionnaireService = $questionnaireService;
        $this->authService = $authService;
    }

    public function newCase()
    {
        return view('editcase');
    }

    public function editCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case !== null && $this->caseService->canAccess($case)) {
            return view('editcase', [
                'case' => $case
            ]);
        } else {
            return redirect()->route('cases-list');
        }
    }

    public function listCases()
    {
        $isPlanner = $this->authService->hasPlannerRole();

        return view('caseoverview', [
            'isPlanner' => $isPlanner
        ]);
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
            $pairingCode = $this->caseService->createPairingCodeForCase($case);
            $isDraftCase = $case->caseStatus() == CovidCase::STATUS_DRAFT;

            // When we show the pairingcode for a new case, we mark the case as 'open'.
            if ($isDraftCase) {
                $this->caseService->openCase($case);
            }
            return view('paircase', ['case' => $case, 'pairingCode' => $pairingCode, 'includeQuestionNumber' => $isDraftCase]);
        }
        return redirect()->route('cases-list');
    }

    /**
     * Trigger healthauthority-api to export case data.
     * Not to be confused with exporting case data to HPZone.
     *
     * @param $caseUuid
     * @return RedirectResponse
     */
    public function notifyCaseUpdate($caseUuid): RedirectResponse
    {
        $case = $this->caseService->getCase($caseUuid);

        if ($case === null || !$this->caseService->canAccess($case)) {
            // This is not the CovidCase you are looking for
            return redirect()->intended('/');
        }

        if ($this->caseService->notifyCaseUpdate($case)) {
            request()->session()->flash('message', 'Case klaargezet voor index');
        } else {
            request()->session()->flash('message', 'Fout bij klaarzetten case voor index');
        }

        return redirect()->intended('/case/' . $caseUuid);
    }

}
