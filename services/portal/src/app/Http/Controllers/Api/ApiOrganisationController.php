<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\BcoPhaseUpdateRequest;
use App\Http\Requests\Api\Organisation\CurrentOrganisationUpdateDecoder;
use App\Http\Requests\Api\Organisation\CurrentOrganisationUpdateRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\Organisation\CurrentOrganisationEncoder;
use App\Http\Responses\Organisation\OrganisationFilterEncoder;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use App\Services\AuthenticationService;
use App\Services\OrganisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;

use function abort_if;

class ApiOrganisationController extends ApiController
{
    private AuthenticationService $authService;
    private OrganisationService $organisationService;

    public function __construct(
        AuthenticationService $authService,
        OrganisationService $organisationService,
    ) {
        $this->authService = $authService;
        $this->organisationService = $organisationService;
    }

    /**
     * List organisations
     */
    #[SetAuditEventDescription('Organisaties opgehaald')]
    public function listOrganisations(Request $request): EncodableResponse
    {
        $organisations = $this->organisationService->listOrganisations();
        return $this->responseForOrganisationFilters($organisations);
    }

    private function responseForOrganisationFilters(Collection $organisations): EncodableResponse
    {
        return
            EncodableResponseBuilder::create($organisations)
            ->withContext(static function (EncodingContext $context): void {
                    $context->registerDecorator(Organisation::class, new OrganisationFilterEncoder());
            })
                ->build();
    }

    /**
     * Update current organisation
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Huidige organisatie bijgewerkt')]
    public function updateCurrentOrganisation(
        CurrentOrganisationUpdateRequest $updateRequest,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $organisation = $this->authService->getSelectedOrganisation();
        abort_if($organisation === null, 404);

        $auditEvent->object(AuditObject::create('organisation', $organisation->uuid));

        $container = $updateRequest->getDecodingContainer();
        $container->getContext()->registerDecorator(Organisation::class, new CurrentOrganisationUpdateDecoder());
        $container->decodeObject(Organisation::class, $organisation);
        $this->organisationService->updateOrganisation($organisation);
        return $this->responseForCurrentOrganisation($organisation);
    }

    /**
     * Update current organization BCO phase
     */
    #[SetAuditEventDescription('Huidige organisatie BCO fase bijgewerkt')]
    public function updateCurrentOrganisationBcoPhase(
        BcoPhaseUpdateRequest $request,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $organisation = $this->authService->getSelectedOrganisation();
        abort_if($organisation === null, 404);

        $auditEvent->object(AuditObject::create('organisation', $organisation->uuid));
        $this->organisationService->updateOrganisationBcoPhase($organisation, $request->getBcoPhase());

        return $this->responseForCurrentOrganisation($organisation);
    }

    private function responseForCurrentOrganisation(Organisation $organisation): EncodableResponse
    {
        return
            EncodableResponseBuilder::create($organisation)
            ->withContext(static function (EncodingContext $context): void {
                    $context->registerDecorator(Organisation::class, new CurrentOrganisationEncoder());
            })
                ->build();
    }
}
