<?php

namespace App\Http\Controllers;

use App\Models\CovidCase;
use App\Repositories\CaseRepository;
use Illuminate\Http\Request;


class CaseController extends Controller
{
    private CaseRepository $caseRepository;

    public function __construct(CaseRepository $caseRepository)
    {
        $this->caseRepository = $caseRepository;
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
            return view('draftcase', ['case' => $case]);
        } else {
            return redirect()->intended('/');
        }
    }

    public function editCase($caseUuid)
    {
        $case = $this->caseRepository->getCase($caseUuid);

        if ($case != null && $this->verifyCaseAccess($case)) {
            return view('editcase', ['case' => $case]);
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
            $case->status = 'open';

            $this->caseRepository->updateCase($case);
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
