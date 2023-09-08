<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Attribute\SetAuditEventDescription;

use function view;

class CallcenterController extends Controller
{
    #[SetAuditEventDescription('Zoek dossier')]
    public function search(): View
    {
        return view('callcenter-search');
    }
}
