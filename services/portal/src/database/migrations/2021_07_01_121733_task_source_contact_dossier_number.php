<?php

declare(strict_types=1);

use App\Models\Task;
use App\Services\Task\TaskDecryptableDefiner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;

class TaskSourceContactDossierNumber extends Migration
{
    public function up(): void
    {
        /** @var EncryptionHelper $encryptionHelper */
        $encryptionHelper = app(EncryptionHelper::class);
        /** @var TaskDecryptableDefiner $taskDecryptableDefiner */
        $taskDecryptableDefiner = app(TaskDecryptableDefiner::class);

        DB::table('task')
            ->where('task_group', '!=', 'contact')
            ->whereNotNull('general')
            ->chunkById(100, static function ($tasks) use ($encryptionHelper, $taskDecryptableDefiner): void {
                foreach ($tasks as $task) {
                    $modelTask = new Task();
                    $modelTask->createdAt = $task->created_at;

                    if (!$taskDecryptableDefiner->isDecryptable($modelTask)) {
                        continue;
                    }

                    $general = json_decode($encryptionHelper->unsealStoreValue($task->general));

                    if (!isset($general->reference)) {
                        continue;
                    }

                    DB::table('task')
                        ->where('uuid', $task->uuid)
                        ->update(['dossier_number' => $general->reference]);
                }
            }, 'uuid');
    }

    public function down(): void
    {
        // No migration needed to roll back
    }
}
