<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Models\Task;

final class TaskEncoder
{
    private TaskDecryptableDefiner $taskDecryptableDefiner;

    public function __construct(TaskDecryptableDefiner $taskDecryptableDefiner)
    {
        $this->taskDecryptableDefiner = $taskDecryptableDefiner;
    }

    public function encode(Task $task): array
    {
        $encoded = (array) $task;
        $encoded['dateOfLastExposure'] = $task->dateOfLastExposure ? $task->dateOfLastExposure->format('Y-m-d') : null;
        $encoded['accessible'] = $this->taskDecryptableDefiner->isDecryptable($task);
        return $encoded;
    }
}
