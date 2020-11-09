<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\Eloquent\EloquentUser;
use App\Models\Organisation;
use Illuminate\Support\Str;

class DbUserRepository implements UserRepository
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
                                           array $organisationUuids): EloquentUser
    {
        $dbUser = EloquentUser::where('external_id', $externalId)->get()->first();

        if (!$dbUser) {
            // User doesn't exist, create.
            $dbUser = new EloquentUser();
            $dbUser->external_id = $externalId;
        }

        $dbUser->email = $email;
        $dbUser->name = $name;
        $dbUser->roles = implode(',', $roles);

        $dbUser->save();

        // Refresh organisations.
        $dbUser->organisations()->sync($organisationUuids);

        return $dbUser;

    }

    public function bcoUserFromEloquentUser(EloquentUser $dbUser): BCOUser
    {
        $user = new BCOUser();
        $user->uuid = $dbUser->uuid;
        $user->email = $dbUser->email;
        $user->name = $dbUser->name;
        $user->externalId = $dbUser->external_id;
        $user->roles = explode(',', $dbUser->roles);

        foreach ($dbUser->organisations as $dbOrganisation)
        {
          //  var_dump($dbOrganisation);
            $organisation = new Organisation();
            $organisation->uuid = $dbOrganisation->uuid;
            $organisation->externalId = $dbOrganisation->external_id;
            $organisation->name = $dbOrganisation->name;
            $user->organisations[] = $organisation;
        }

        return $user;
    }
}
