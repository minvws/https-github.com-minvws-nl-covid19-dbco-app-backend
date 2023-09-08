<?php

declare(strict_types=1);

namespace App\Models\Disease;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Events\QueuedClosure;
use RuntimeException;
use Throwable;

/**
 * @property int $version
 * @property VersionStatus $status
 *
 * @use HasEvents<static>
 */
trait HasVersionStatus
{
    protected function getHasVersionStatusSiblingsRelation(): HasMany
    {
        throw new RuntimeException(__METHOD__ . ' . not implemented!');
    }

    public static function publishing(QueuedClosure|Closure|array|string $callback): void
    {
        static::registerModelEvent('publishing', $callback);
    }

    public static function published(QueuedClosure|Closure|array|string $callback): void
    {
        static::registerModelEvent('published', $callback);
    }

    public static function archiving(QueuedClosure|Closure|array|string $callback): void
    {
        static::registerModelEvent('archiving', $callback);
    }

    public static function archived(QueuedClosure|Closure|array|string $callback): void
    {
        static::registerModelEvent('archived', $callback);
    }

    public static function bootHasVersionStatus(): void
    {
        static::creating(static function ($model): void {
            if ($model->status === VersionStatus::Draft && $model->version === null) {
                $model->version = $model->getNextVersionNumber();
            }
        });
    }

    public function initializeHasVersionStatus(): void
    {
        $this->attributes['status'] = VersionStatus::Draft;
        $this->casts['status'] = VersionStatus::class;

        $this->addObservableEvents([
            'publishing',
            'published',
            'archiving',
            'archived',
        ]);
    }

    protected function getNextVersionNumber(): int
    {
        return ($this->getHasVersionStatusSiblingsRelation()->max('version') ?? 0) + 1;
    }

    /**
     * Publish object.
     *
     * @throws Throwable
     */
    public function publish(): bool
    {
        $this->refresh(); // get all data

        if ($this->status !== VersionStatus::Draft) {
            throw new Exception('Only draft versions can be published!');
        }

        return $this->getConnection()->transaction(function () {
            if ($this->fireModelEvent('publishing') === false) {
                return false;
            }

            // archive currently published version
            $published = $this->getHasVersionStatusSiblingsRelation()->where('status', '=', VersionStatus::Published)->first();
            if ($published instanceof static) {
                $published->status = VersionStatus::Archived;
                $published->save();
            }

            // update status to published
            $this->status = VersionStatus::Published;

            $this->save();

            $this->fireModelEvent('published', false);

            return true;
        });
    }

    /**
     * Archive object.
     *
     * @throws Throwable
     */
    public function archive(): bool
    {
        if ($this->status !== VersionStatus::Published) {
            throw new Exception('Only published versions can be archived!');
        }

        return $this->getConnection()->transaction(function () {
            if ($this->fireModelEvent('archiving') === false) {
                return false;
            }

            $this->status = VersionStatus::Archived;
            $this->save();

            $this->fireModelEvent('archived', false);

            return true;
        });
    }
}
