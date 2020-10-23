<?php

namespace App\Http\Controllers;

use App\Models\CovidCase;
use Illuminate\Http\Request;


class CaseController extends Controller
{
    public function newCase()
    {
        // Because we want to show the new case immediately, we create a draft case.
        $case = new CovidCase();

        $case->save();

        return view('newcase', ['case' => $case]);
    }

    public function saveCase(Request $request)
    {
        // TODO: happyflow is never enough.

        $uuid = $request->input('caseId');

        $cases = CovidCase::where('uuid', $uuid);
        $case = $cases->first();

        $case->name = $request->input('name');
        $case->status = 'open';

        $case->save();

        return redirect()->intended('/');
    }
}
