<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AuditObjectHelper;
use App\Models\Eloquent\EloquentCase;
use Illuminate\Contracts\View\View;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;

use function view;

class CallToActionController extends Controller
{
    public function index(): View
    {
        return view('call-to-action');
    }

    public function create(EloquentCase $case, AuditEvent $auditEvent): View
    {
        $caseAuditObject = AuditObject::create('case', $case->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $case);
        $auditEvent->object($caseAuditObject);

        return view('create-call-to-action', [
            'case' => $case,
        ]);
    }
}
