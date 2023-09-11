<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface CaseStatusRepository
{
    /**
     * Transition the index_status when a timeout occurs on pairing the case
     * to a client,
     *
     * @return int the number of rows affected
     */
    public function updateTimeoutIndexStatus(int $limit): int;

    /**
     * Transition the index_status to expired when a paired client has not
     * submitted withing the submit window (windows_expires_at).
     *
     * @return Collection<string> Collection of updated case UUIDs
     */
    public function updateExpiredIndexStatus(int $limit): Collection;
}
