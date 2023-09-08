<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentUser;
use DateTimeInterface;
use Illuminate\Support\Collection;

interface UserRepository
{
    /**
     * Note, we're normally not exposing EloquentObjects outside our service layer, however
     * Laravel's authentication mechanism either needs a database row or an eloquent object,
     * and the database authentication classes don't support our custom 'uuid' id column. That's
     * why for authentication purposes we're passing an EloquentUser around.
     */
    public function upsertUserByExternalId(
        string $externalId,
        string $name,
        array $roles,
        string $organisationUuid,
    ): EloquentUser;

    public function getByUuid(string $uuid): ?EloquentUser;

    /**
     * @return Collection<EloquentUser>
     */
    public function getAssignableUsers(string $organisationUuid, DateTimeInterface $lastLoginThreshold): Collection;
}
