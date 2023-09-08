<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Responses\EncodableResponse;
use App\Repositories\CaseLabelRepository;
use App\Services\AuthenticationService;
use MinVWS\Audit\Attribute\SetAuditEventDescription;

class ApiCaseLabelController extends ApiController
{
    private AuthenticationService $authenticationService;
    private CaseLabelRepository $caseLabelRepository;

    public function __construct(
        AuthenticationService $authenticationService,
        CaseLabelRepository $caseLabelRepository,
    ) {
        $this->authenticationService = $authenticationService;
        $this->caseLabelRepository = $caseLabelRepository;
    }

    /**
     * Get case labels
     */
    #[SetAuditEventDescription('Case labels opgehaald')]
    public function getCaseLabels(): EncodableResponse
    {
        $organisation = $this->authenticationService->getRequiredSelectedOrganisation();

        return new EncodableResponse($this->caseLabelRepository->getByOrganisation($organisation));
    }
}
