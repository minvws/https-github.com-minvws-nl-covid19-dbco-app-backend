<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;

class DbCaseStatusRepository implements CaseStatusRepository
{
    public function updateTimeoutIndexStatus(int $limit): int
    {
        return EloquentCase::query()
            ->where('bco_status', BCOStatus::open()->value)
            ->where('pairing_expires_at', '<=', DB::raw('NOW()'))
            ->where('index_status', '<>', IndexStatus::expired()->value)
            ->where('index_status', '<>', IndexStatus::timeout()->value)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->update([
                'index_status' => IndexStatus::timeout()->value,
            ]);
    }

    public function updateExpiredIndexStatus(int $limit): Collection
    {
        $query = EloquentCase::query()
            ->select(['uuid'])
            ->where('bco_status', BCOStatus::open()->value)
            ->where('index_status', '<>', IndexStatus::expired()->value)
            ->whereNull('index_submitted_at')
            ->where('window_expires_at', '<=', DB::raw('NOW()'))
            ->orderByDesc('updated_at')
            ->limit($limit);

        /** @var Collection<string> $expiredCases */
        $expiredCases = $query->get('uuid')->pluck('uuid');

        $query->update([
            'index_status' => IndexStatus::expired()->value,
        ]);

        return $expiredCases;
    }
}
