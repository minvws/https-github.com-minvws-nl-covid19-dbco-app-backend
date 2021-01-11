<?php

namespace App\Http\Controllers\Api;

use App\Models\CovidCase;
use App\Models\Task;
use App\Services\AuthenticationService;
use App\Services\CaseService;
use App\Services\QuestionnaireService;
use App\Services\TaskService;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;


class ApiCaseController extends ApiController
{
    private CaseService $caseService;
    private TaskService $taskService;
    private QuestionnaireService $questionnaireService;
    private AuthenticationService $authService;

    public function __construct(CaseService $caseService,
                                TaskService $taskService,
                                QuestionnaireService $questionnaireService,
                                AuthenticationService $authService)
    {
        $this->caseService = $caseService;
        $this->taskService = $taskService;
        $this->questionnaireService = $questionnaireService;
        $this->authService = $authService;
    }

    public function getCase($caseUuid)
    {
        $case = $this->caseService->getCase($caseUuid, false);

        if ($case === null) {
            return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['case' => $case]);
    }

    public function myCases()
    {
        $myCases = $this->caseService->myCases();

        $this->viewEnhancements($myCases);

        return response()->json(['cases' => $myCases], Response::HTTP_OK);
    }

    public function allCases()
    {
        $allCases = $this->caseService->organisationCases();

        $this->viewEnhancements($allCases);

        return response()->json(['cases' => $allCases], Response::HTTP_OK);
    }

    private function viewEnhancements($cases)
    {
        // Enrich my cases data with some view level helper data
        foreach ($cases as $case) {
            $case->editCommand = route('case-edit', [$case->uuid]);

            $case->statusIcon = asset("/images/status_".$case->caseStatus().".svg");
            $case->statusLabel = CovidCase::statusLabel($case->caseStatus());
        }
    }

    public function assignCase(Request $request)
    {
        $caseUuid = $request->input('caseId');
        $userUuid = $request->input('userId');

        $case = $this->caseService->getCase($caseUuid);

        if ($case === null) {
            return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
            return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->authService->isInOrganisation($case->organisationUuid)) {
            return response()->json(['error' => 'Je kunt alleen cases van je eigen organisatie toewijzen'], Response::HTTP_FORBIDDEN);
        }

        if ($this->caseService->assignCase($case, $userUuid)) {
            return response()->json(['success' => 'success'], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Onbekende fout'], Response::HTTP_BAD_REQUEST);
    }

    public function postCase(Request $request)
    {
        $caseData = $request->input('case');
        $caseUuid = $caseData['uuid'];

        $validatedData = $request->validate([
            'case.name' => 'required|max:255',
            'case.caseId' => 'max:255'
        ]);

        if ($caseUuid == '') {
            // New case!
            $case = new CovidCase();
        } else {
            // Existing case
            $case = $this->caseService->getCase($caseUuid);

            if ($case === null) {
                return response()->json(['error' => "Deze case bestaat niet (meer)"], Response::HTTP_BAD_REQUEST);
            }

            if (!$this->caseService->canAccess($case) && !$this->authService->hasPlannerRole()) {
                return response()->json(['error' => 'Geen toegang tot de case'], Response::HTTP_FORBIDDEN);
            }
        }

        $case->name = $validatedData['case']['name'];
        $case->caseId = $validatedData['case']['caseId'];
        $case->symptomatic = $caseData['symptomatic'];
        $case->dateOfSymptomOnset = isset($caseData['dateOfSymptomOnset']) ? Date::parse($caseData['dateOfSymptomOnset']) : null;
        $case->dateOfTest = isset($caseData['dateOfTest']) ? Date::parse($caseData['dateOfTest']) : null;

        if ($caseUuid == '') {
            $result = false;
            $case = $this->caseService->createCase($case);
            if ($case) {
                $result = true;
            }
        } else {
            $result = $this->caseService->updateCase($case);
        }
        if ($result) {
            return response()->json(['case' => $case]);
        }
        return response()->json(['error' => 'Onbekende fout bij opslag'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
