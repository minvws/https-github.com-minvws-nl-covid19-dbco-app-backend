<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\PseudoBsnLookupRequest;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use App\Repositories\Bsn\BsnException;
use App\Services\AuthenticationService;
use App\Services\Bsn\BsnService;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;

use function response;
use function substr;

class ApiBsnController extends ApiController
{
    private AuthenticationService $authenticationService;
    private BsnService $bsnService;

    public function __construct(
        AuthenticationService $authenticationService,
        BsnService $bsnService,
    ) {
        $this->authenticationService = $authenticationService;
        $this->bsnService = $bsnService;
    }

    #[SetAuditEventDescription('Pseudo BSN opgezocht')]
    public function pseudoBsnLookup(PseudoBsnLookupRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->actionCode(AuditEvent::ACTION_READ);
        $bsnAuditObject = AuditObject::create('pseudo-bsn');
        $auditEvent->object($bsnAuditObject);

        $fullBsn = $request->getBsn();
        $dateOfBirth = $request->getPostDateOfBirth();
        $lastThreeDigits = $request->getLastThreeDigits();
        $postalCode = $request->getPostPostalCode();
        $houseNumber = $request->getPostHouseNumber();
        $houseNumberSuffix = $request->getPostHouseNumberSuffix();

        if ($lastThreeDigits === null && $fullBsn !== null) {
            $lastThreeDigits = substr($fullBsn, -3);
        }

        try {
            if ($fullBsn !== null) {
                $pseudoBsn = $this->bsnService->pseudoBsnLookupWithBsn(
                    $fullBsn,
                    $dateOfBirth,
                    $postalCode,
                    $houseNumber,
                    $houseNumberSuffix,
                    $this->getOrganisationExternalId(),
                );

                $bsnAuditObject
                    ->identifier($pseudoBsn->getGuid())
                    ->detail('results-found', 'yes');

                return response()->json($pseudoBsn->toArray());
            }

            if ($lastThreeDigits === null) {
                throw new BsnException('last three digits of bsn cannot be determined');
            }

            $bsnAuditObject
                ->detail('dateOfBirth', $dateOfBirth)
                ->detail('lastThreeDigits', $lastThreeDigits)
                ->detail('postalCode', $postalCode)
                ->detail('houseNumber', $houseNumber)
                ->detail('houseNumberSuffix', $houseNumberSuffix);

            $pseudoBsn = $this->bsnService->pseudoBsnLookupWithLastThreeDigits(
                $lastThreeDigits,
                $dateOfBirth,
                $postalCode,
                $houseNumber,
                $houseNumberSuffix,
                $this->getOrganisationExternalId(),
            );

            $bsnAuditObject
                ->identifier($pseudoBsn->getGuid())
                ->detail('results-found', 'yes');

            return response()->json($pseudoBsn->toArray());
        } catch (BsnException $bsnException) {
            $bsnAuditObject->detail('results-found', 'no');
            return response()->json(['error' => $bsnException->getMessage()]);
        }
    }

    /**
     * @throws BsnException
     */
    private function getOrganisationExternalId(): string
    {
        $requiredSelectedOrganisation = $this->authenticationService->getRequiredSelectedOrganisation();

        if ($requiredSelectedOrganisation->type === OrganisationType::regionalGGD()) {
            return $requiredSelectedOrganisation->external_id;
        }

        /** @var EloquentOrganisation|null $regionalOrganisations */
        $regionalOrganisations = $requiredSelectedOrganisation->regionalOrganisations()->withoutGlobalScopes()->first();
        if ($regionalOrganisations === null) {
            throw new BsnException('No parent (regional) organisation found');
        }

        return $regionalOrganisations->external_id;
    }
}
