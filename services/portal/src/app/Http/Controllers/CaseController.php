<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AuditObjectHelper;
use App\Models\Eloquent\EloquentCase;
use Illuminate\Contracts\View\View;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;

use function view;

class CaseController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {
    }

    #[SetAuditEventDescription('Case geopend in BCO weergave')]
    public function editCase(EloquentCase $case, AuditEvent $auditEvent): View
    {
        $caseAuditObject = AuditObject::create('case', $case->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $case);
        $auditEvent->object($caseAuditObject);

        return view('editcase', [
            'case' => $case,
        ]);
    }

    #[SetAuditEventDescription('Gebruikers cases opgehaald')]
    public function listUserCases(): View
    {
        $this->auditService->setEventExpected(false);

        return view('caseoverview');
    }
}
