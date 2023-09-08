<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Services\AuditService;

use function view;

class PlaceController extends Controller
{
    private AuditService $auditService;

    public function __construct(
        AuditService $auditService,
    ) {
        $this->auditService = $auditService;
    }

    /**
     * Edit place
     *
     * @param String $place
     */
    #[SetAuditEventDescription('Context geopend in detail weergave')]
    public function editPlace(string $place): View
    {
        return view('editplace', [
            'place' => $place,
        ]);
    }

    #[SetAuditEventDescription('Toon lijst met contexten')]
    public function listPlaces(): View
    {
        $this->auditService->setEventExpected(false);

        return view('placesoverview');
    }
}
