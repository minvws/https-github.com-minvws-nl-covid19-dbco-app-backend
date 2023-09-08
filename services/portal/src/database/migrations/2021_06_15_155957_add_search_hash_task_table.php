<?php

declare(strict_types=1);

use App\Helpers\SearchableHash;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Services\TaskFragmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class AddSearchHashTaskTable extends Migration
{
    public function up(): void
    {
        // Columns to create, only if they don't exist
        $columns = [
            'search_date_of_birth',
            'search_email',
            'search_phone',
        ];

        foreach ($columns as $column) {
            if (!Schema::hasColumn('task', $column)) {
                Schema::table('task', static function (Blueprint $table) use ($column): void {
                    $table->string($column)->nullable();
                });
            }
        }

        /** @var TaskFragmentService $taskFragmentService */
        $taskFragmentService = app(TaskFragmentService::class);
        /** @var SearchableHash $searchableHash */
        $searchableHash = app(SearchableHash::class);

        // Only grab the tasks that haven't been parsed yet (due to timeouts)
        $tasks = EloquentTask::query()->where(static function (Builder $query): void {
            $query->whereNull('search_date_of_birth')
                ->whereNull('search_email')
                ->whereNull('search_phone');
        })->get();

        foreach ($tasks as $task) {
            try {
                $fragments = $taskFragmentService->loadFragments($task->uuid, ['general', 'personalDetails']);
                /** @var General $general */
                $general = $fragments['general'];
                /** @var PersonalDetails $personalDetails */
                $personalDetails = $fragments['personalDetails'];

                if ($general->lastname && $personalDetails->dateOfBirth && $task->search_date_of_birth === null) {
                    $task->search_date_of_birth = $searchableHash->hashForLastNameAndDateOfBirth(
                        $general->lastname,
                        $personalDetails->dateOfBirth,
                    );
                }

                if ($general->lastname && $general->email && $task->search_email === null) {
                    $task->search_email = $searchableHash->hashForLastNameAndEmail($general->lastname, $general->email);
                }

                if ($general->lastname && $general->phone && $task->search_phone === null) {
                    $task->search_phone = $searchableHash->hashForLastNameAndPhone($general->lastname, $general->phone);
                }

                $task->save();
            } catch (Throwable $exception) {
                // Something went wrong with the fragments, we skip updating the task
            }
        }
    }

    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn(['search_date_of_birth', 'search_email', 'search_phone']);
        });
    }
}
