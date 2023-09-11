<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Config;
use App\Services\Chores\ChoreService;
use Illuminate\Console\Command;

use function sprintf;

class PurgeStaleChores extends Command
{
    protected $signature = 'chores:purge-stale {--chunk} {--usleep}';

    protected $description = 'Purge chores that have been gone stale';

    public function handle(ChoreService $choreService): int
    {
        $chunkSize = (int) $this->option('chunk') ?: Config::integer('misc.commands.purge_stale_chores.default_chunk_size');
        $usleep = (int) $this->option('usleep') ?: Config::integer('misc.commands.purge_stale_chores.default_usleep');
        $trashed = 0;

        $choreService->chunkForceDeleteAllChoresWithoutResourceable(
            $chunkSize,
            $usleep,
            static function () use (&$trashed): void {
                $trashed++;
            },
        );

        $this->info(sprintf('%s stale chores have been deleted', $trashed));

        return self::SUCCESS;
    }
}
