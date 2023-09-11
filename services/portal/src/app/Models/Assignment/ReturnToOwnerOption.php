<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use MinVWS\Codable\EncodingContainer;

use function array_key_first;
use function count;
use function is_null;

class ReturnToOwnerOption extends LeafOption
{
    private array $owners = [];

    public function getLabel(): string
    {
        return 'Verplaatsen naar ' . $this->getOwnerLabel();
    }

    private function getOwnerLabel(): string
    {
        if (count($this->owners) === 1) {
            return $this->owners[array_key_first($this->owners)];
        }

        return 'eigenaar GGDs';
    }

    public function addOwner(Organisation $organisation): void
    {
        $this->owners[$organisation->uuid] = $organisation->name;
    }

    public function encode(EncodingContainer $container): void
    {
        parent::encode($container);

        $container->assignment->assignedOrganisationUuid = null;
    }

    public function isAvailable(): bool
    {
        return $this->isEnabled(); // DBCO-2437
    }

    public function updateForCase(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull, Cache $cache): void
    {
        $selectedOrganisationUuid = $selectedOrganisation->getRawOriginal('uuid') ?? $selectedOrganisation->uuid;
        $ownerOrganisationUuid = $case->getRawOriginal('organisation_uuid') ?? $case->organisation_uuid;
        $assignedOrganisationUuid = $case->getRawOriginal('assigned_organisation_uuid') ?? $case->assigned_organisation_uuid;
        $assignedUserUuid = $case->getRawOriginal('assigned_user_uuid') ?? $case->assigned_user_uuid;

        // case can be returned to owner if:
        // 1.  it is assigned to a different organisation
        // 2a. the current users works for that different organisation
        // 2b. ór the current user works for the owner organisation and the case is not currently assigned to a user
        $incrementEnabled = !is_null($assignedOrganisationUuid) &&
            $assignedOrganisationUuid !== $ownerOrganisationUuid &&
            ($assignedOrganisationUuid === $selectedOrganisationUuid || $assignedUserUuid === null);
        $this->incrementSelected(false);
        $this->incrementEnabled($incrementEnabled);
        $this->addOwner($case->organisation);
    }
}
