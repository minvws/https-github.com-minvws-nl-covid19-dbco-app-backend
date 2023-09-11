<?php

declare(strict_types=1);

namespace App\Services\Export\Helpers;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\ExportClient;
use App\Services\Export\Exceptions\ExportAuthorizationException;

class ExportAuthorizationHelper
{
    /**
     * @throws ExportAuthorizationException
     */
    public function validateOrganisationAccessForClient(EloquentOrganisation $organisation, ExportClient $client): void
    {
        if ($client->organisations->first(static fn (EloquentOrganisation $o) => $o->uuid === $organisation->uuid) === null) {
            throw new ExportAuthorizationException();
        }
    }
}
