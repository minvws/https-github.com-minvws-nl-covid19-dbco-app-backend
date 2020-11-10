<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\Eloquent\EloquentUser;

interface UserRepository
{
    /**
     * Note, we're normally not exposing EloquentObjects outside our service layer, however
     * Laravel's authentication mechanism either needs a database row or an eloquent object,
     * and the database authentication classes don't support our custom 'uuid' id column. That's
     * why for authentication purposes we're passing an EloquentUser around.
     * @param string $externalId
     * @param string $name
     * @param string|null $email
     * @param array $roles
     * @param array $organisationUuids
     * @return EloquentUser
     */
    public function upsertUserByExternalId(string $externalId,
                                           string $name,
                                           ?string $email,
                                           array $roles,
                                           array $organisationUuids): EloquentUser;

    public function bcoUserFromEloquentUser(EloquentUser $user): BCOUser;
}
