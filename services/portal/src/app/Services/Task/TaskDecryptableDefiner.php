<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Models\Task;

interface TaskDecryptableDefiner
{
    public function isDecryptable(Task $task): bool;
}
