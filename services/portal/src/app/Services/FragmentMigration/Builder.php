<?php

declare(strict_types=1);

namespace App\Services\FragmentMigration;

use App\Models\Task;
use App\Services\Task\TaskDecryptableDefiner;
use Carbon\CarbonImmutable;
use Closure;
use DateTime;
use DateTimeInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use stdClass;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use function array_map;
use function array_merge;
use function json_decode;
use function json_encode;
use function preg_replace_callback;
use function sprintf;
use function strtolower;

class Builder
{
    private OwnerType $type;
    private array $fragments;

    private EncryptionHelper $encryptionHelper;
    private TaskDecryptableDefiner $taskDecryptableDefiner;
    private ConsoleOutput $output;

    public function __construct(
        OwnerType $type,
        array $fragments,
        EncryptionHelper $encryptionHelper,
        TaskDecryptableDefiner $taskDecryptableDefiner,
        ConsoleOutput $output,
    ) {
        $this->type = $type;
        $this->fragments = $fragments;
        $this->encryptionHelper = $encryptionHelper;
        $this->taskDecryptableDefiner = $taskDecryptableDefiner;
        $this->output = $output;
    }

    public function update(callable $callback, ?Closure $tick = null): UpdateResult
    {
        $updatedCount = 0;
        $skippedCount = 0;

        // chunkById is way faster than using an offset when we have a large amount of rows.
        // NOTE:
        // If you follow the Eloquent code it might look like it only works for integers but it also works for strings.
        $this->buildSelect()->chunkById(100, function ($rows) use ($callback, &$updatedCount, &$skippedCount, $tick): void {
            foreach ($rows as $row) {
                $updated = $this->updateRow($row, $callback);

                if ($updated) {
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }

                if ($tick) {
                    $tick();
                }
            }
        }, 'uuid');

        return new UpdateResult($updatedCount, $skippedCount);
    }

    public function updateWithProgress(callable $callback): UpdateResult
    {
        $total = DB::table($this->type->getTable())->count('uuid');
        if ($total === 0) {
            return new UpdateResult(0, 0);
        }

        $this->output->writeln(sprintf('WARNING: Going to update %d rows, this might take a while!', $total));

        $progress = new ProgressBar($this->output, $total);
        $progress->start();

        $result = $this->update(
            $callback,
            static fn () => $progress->advance()
        );

        $progress->finish();

        $this->output->writeln('');
        $this->output->writeln(sprintf('Updated: %d, Skipped: %d', $result->getUpdatedCount(), $result->getSkippedCount()));

        return $result;
    }

    private function updateRow(stdClass $row, callable $callback): bool
    {
        if (!$this->isDecryptable($row)) {
            return false;
        }

        $fragmentObjects = $this->collectFragmentObjects($row, $this->fragments);

        $shouldUpdate = $callback($row, $fragmentObjects);
        if ($shouldUpdate === false) {
            return false;
        }

        $updates = $this->collectFragmentObjectUpdates(
            $fragmentObjects,
            $this->type->getStorageTerm(),
            new DateTime($row->created_at),
        );

        if (empty($updates)) {
            return false;
        }

        DB::table($this->type->getTable())
            ->where('uuid', $row->uuid)
            ->update($updates);

        return true;
    }

    private function buildSelect(): QueryBuilder
    {
        $builder = DB::table($this->type->getTable())->orderBy($this->type->getTable() . '.uuid');

        $fragmentColumns = array_map(
            fn ($n) => $this->type->getTable() . '.' . $this->columnNameForFragmentName($n),
            $this->fragments,
        );

        if ($this->type === OwnerType::context()) {
            $builder
                ->select(array_merge(['context.uuid', 'covidcase.created_at'], $fragmentColumns))
                ->join('covidcase', 'covidcase.uuid', '=', 'context.covidcase_uuid');
        } else {
            $builder->select(array_merge(
                [$this->type->getTable() . '.uuid', $this->type->getTable() . '.created_at'],
                $fragmentColumns,
            ));
        }

        return $builder;
    }

    private function columnNameForFragmentName(string $fragmentName): string
    {
        // alternateContact => alternate_contact
        return (string) preg_replace_callback('/[A-Z]/', static fn($m) => '_' . strtolower($m[0]), $fragmentName);
    }

    private function collectFragmentObjects(object $model, array $fragments): array
    {
        $fragmentObjects = [];

        foreach ($fragments as $fragment) {
            $columnName = $this->columnNameForFragmentName($fragment);
            $json = $this->encryptionHelper->unsealOptionalStoreValue($model->$columnName ?? null);

            if ($json !== null) {
                $fragmentObjects[$fragment] = json_decode($json);
            }
        }

        return $fragmentObjects;
    }

    private function isDecryptable(stdClass $row): bool
    {
        if ($this->type !== OwnerType::task()) {
            return true;
        }

        $task = new Task();
        $task->createdAt = new CarbonImmutable($row->created_at);
        return $this->taskDecryptableDefiner->isDecryptable($task);
    }

    private function collectFragmentObjectUpdates(
        array $fragmentObjects,
        StorageTerm $storageTerm,
        DateTimeInterface $referenceDateTime,
    ): array {
        $updates = [];

        /** @var string $fragment */
        foreach ($fragmentObjects as $fragment => $fragmentObject) {
            $columnName = $this->columnNameForFragmentName($fragment);

            $json = json_encode($fragmentObject);
            if ($json !== false) {
                $updates[$columnName] = $this->encryptionHelper->sealStoreValue($json, $storageTerm, $referenceDateTime);
            }
        }

        return $updates;
    }
}
