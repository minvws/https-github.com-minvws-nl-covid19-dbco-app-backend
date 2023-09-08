<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Models\Task;
use Carbon\CarbonImmutable;
use LogicException;

final class DefaultTaskDecryptableDefiner implements TaskDecryptableDefiner
{
    private int $days;

    public function __construct(int $days)
    {
        $this->days = $days;
    }

    public function isDecryptable(Task $task): bool
    {
        if (!isset($task->createdAt)) {
            throw new LogicException('CreatedAt is not set');
        }

        $createdAtStartOfDay = $task->createdAt->startOfDay();
        $limitStartOfDay = CarbonImmutable::today()->subDays($this->days);

        return $createdAtStartOfDay->isAfter($limitStartOfDay);
    }
}
