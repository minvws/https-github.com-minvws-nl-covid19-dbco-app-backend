<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use Illuminate\Support\Collection;

interface AssignmentTokenService
{
    /**
     * @param array<int,string> $uuids
     */
    public function createTokenForCases(array $uuids, EloquentUser $user, int $ttlExpirationInMinutes = 30): string;

    /**
     * @param Collection<int,TokenResource> $tokenResources
     */
    public function createToken(
        Collection $tokenResources,
        EloquentUser $user,
        int $ttlExpirationInMinutes = 30,
    ): string;

    public function getAudience(): string;

    public function getIssuer(): string;
}
