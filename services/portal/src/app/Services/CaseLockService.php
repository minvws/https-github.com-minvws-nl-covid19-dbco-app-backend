<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CaseLock\CaseLockFoundException;
use App\Exceptions\CaseLock\CaseLockNotFoundException;
use App\Exceptions\CaseLock\CaseLockNotOwnedException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentCaseLock;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

use function config;

class CaseLockService
{
    public function hasCaseLock(EloquentCase $case, ?EloquentUser $user = null): bool
    {
        $caseLock = $this->queryEloquentCaseLock($case)->first();

        if (!$caseLock instanceof EloquentCaseLock) {
            return false;
        }

        return !$user || $caseLock->user_uuid !== $user->uuid;
    }

    public function addCaseLock(EloquentCase $case, EloquentUser $user): EloquentCaseLock
    {
        if ($this->hasCaseLock($case)) {
            throw new CaseLockFoundException();
        }

        $caseLock = new EloquentCaseLock();

        $caseLock->user_uuid = $user->uuid;
        $caseLock->case_uuid = $case->uuid;
        $caseLock->locked_at = CarbonImmutable::now();
        $caseLock->save();

        return $caseLock;
    }

    public function refreshCaseLock(EloquentCase $case, EloquentUser $user): EloquentCaseLock
    {
        $caseLock = $this->getCaseLock($case, $user);

        if ($caseLock === null) {
            throw new CaseLockNotFoundException();
        }

        $caseLock->locked_at = CarbonImmutable::now();
        $caseLock->save();

        return $caseLock;
    }

    public function removeCaseLock(EloquentCase $case, EloquentUser $user): ?bool
    {
        $caseLock = $this->getCaseLock($case, $user);

        if ($caseLock === null) {
            throw new CaseLockNotFoundException();
        }

        return $caseLock->delete();
    }

    /**
     * @throws CaseLockNotFoundException
     */
    public function getCaseLock(EloquentCase $case, ?EloquentUser $user = null): ?EloquentCaseLock
    {
        $caseLock = $this->queryEloquentCaseLock($case)->first();

        if ($caseLock instanceof EloquentCaseLock && $user && $caseLock->user_uuid !== $user->uuid) {
            throw new CaseLockNotOwnedException();
        }

        if (!$caseLock instanceof EloquentCaseLock) {
            return null;
        }

        return $caseLock;
    }

    protected function queryEloquentCaseLock(EloquentCase $case): Builder
    {
        $time = CarbonImmutable::now()->subMinutes((int) config('misc.caseLock.lifetime', 3));

        return EloquentCaseLock::query()
            ->where('case_uuid', $case->uuid)
            ->whereDate('locked_at', '>=', $time)
            ->whereTime('locked_at', '>', $time);
    }

    public function cleanCaseLocks(): void
    {
        $time = CarbonImmutable::now()->subMinutes((int) config('misc.caseLock.lifetime', 3));

        EloquentCaseLock::query()
            ->whereDate('locked_at', '<=', $time)
            ->whereTime('locked_at', '<', $time)
            ->delete();
    }
}
