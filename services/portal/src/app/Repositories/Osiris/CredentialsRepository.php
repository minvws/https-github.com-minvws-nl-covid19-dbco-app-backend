<?php

declare(strict_types=1);

namespace App\Repositories\Osiris;

use App\Dto\Osiris\Client\Credentials;
use App\Models\Eloquent\EloquentOrganisation;

interface CredentialsRepository
{
    public function getForOrganisation(EloquentOrganisation $organisation): Credentials;
}
