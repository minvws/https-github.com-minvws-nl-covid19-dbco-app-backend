<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentUser;
use DateTimeInterface;
use Illuminate\Support\Collection;

use function collect;
use function implode;

class DbUserRepository implements UserRepository
{
    /**
     * @inheritDoc
     */
    public function upsertUserByExternalId(
        string $externalId,
        string $name,
        array $roles,
        string $organisationUuid,
    ): EloquentUser {
        $dbUser = EloquentUser::where('external_id', $externalId)->first();

        if (!$dbUser) {
            // User doesn't exist, create.
            $dbUser = new EloquentUser();
            $dbUser->external_id = $externalId;
        }

        $dbUser->name = $name;
        $dbUser->roles = implode(',', $roles);

        $dbUser->save();

        // Refresh organisations.
        $dbUser->organisations()->sync(collect($organisationUuid));

        return $dbUser;
    }

    public function getByUuid(string $uuid): ?EloquentUser
    {
        return EloquentUser::query()->find($uuid);
    }

    public function getAssignableUsers(string $organisationUuid, DateTimeInterface $lastLoginThreshold): Collection
    {
        return
            EloquentUser::query()
            ->where('user_organisation.organisation_uuid', $organisationUuid)
            ->where('bcouser.last_login_at', '>', $lastLoginThreshold->format('Y-m-d H:i:s'))
            ->select('bcouser.*')
            ->join('user_organisation', 'user_organisation.user_uuid', '=', 'bcouser.uuid')
            ->orderBy('bcouser.name', 'asc')
            ->get();
    }
}
