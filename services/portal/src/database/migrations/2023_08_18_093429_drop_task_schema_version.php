<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration
{
    public function up(): void
    {
        // drop column
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('task_schema_version');
        });
    }

    public function down(): void
    {
        // restore column
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->unsignedInteger('task_schema_version')->nullable();
        });

        // rollback values task_schema_version column
        $query = DB::table('covidcase')
            ->whereNull('task_schema_version')
            ->select(['uuid']);

        $output = new ConsoleOutput();
        $output->writeln('Retrieving count for progress "task_schema_version" update ...');
        $count = $query->count('uuid');
        $output->writeln(sprintf('Started to update "task_schema_version" for %d records...', $count));

        $bar = new ProgressBar($output, $count);
        $bar->start();

        $query
            ->orderBy('uuid')
            ->chunkById(
                100,
                $this->prepareTaskSchemaUpdate($bar),
                'uuid',
            );
    }

    private function prepareTaskSchemaUpdate(ProgressBar $bar): Closure
    {
        return static function (Collection $cases) use ($bar): void {
            $uuids = $cases->pluck('uuid');

            foreach ($uuids as $uuid) {
                $case = EloquentCase::find($uuid);

                if (!$case instanceof EloquentCase) {
                    continue;
                }

                DB::table('covidcase')
                    ->where('uuid', $uuid)
                    ->update(['task_schema_version' => $case->getTaskSchemaVersion()->getVersion()]);

                unset($case);
            }

            $bar->advance($cases->count());
        };
    }
};
