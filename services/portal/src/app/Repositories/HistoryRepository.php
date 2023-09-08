<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\OsirisHistory\OsirisHistoryDto;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisHistory;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class HistoryRepository
{
    use LimitChecker;

    public function __construct(private readonly LoggerInterface $log)
    {
    }

    public function addToOsirisHistory(OsirisHistoryDto $dto): OsirisHistory
    {
        return OsirisHistory::create([
            'case_uuid' => $dto->caseUuid,
            'status' => $dto->status,
            'osiris_status' => $dto->osirisStatus,
            'osiris_validation_response' => $dto->osirisValidationResponse,
        ]);
    }

    public function getOsirisHistory(EloquentCase $case): Collection
    {
        $result = OsirisHistory::where('case_uuid', $case->uuid)
            ->orderByDesc('created_at')
            ->limit($this->hardLimit)
            ->get();

        $this->limitChecker($this->log, $result);

        return $result;
    }
}
