<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Eloquent\EloquentMessage;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

final class DbMessageRepository implements MessageRepository
{
    /**
     * @return Collection<EloquentMessage>
     */
    public function findByCaseUuid(string $caseUuid): Collection
    {
        return EloquentMessage::query()
            ->where('case_uuid', $caseUuid)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return Collection<EloquentMessage>
     */
    public function findByCaseUuidAndTaskUuid(string $caseUuid, ?string $taskUuid): Collection
    {
        return EloquentMessage::query()
            ->where('case_uuid', $caseUuid)
            ->where('task_uuid', $taskUuid)
            ->orderBy('created_at')
            ->get();
    }

    public function save(EloquentMessage $eloquentMessage): void
    {
        $eloquentMessage->save();
    }

    /**
     * @throws RepositoryException
     */
    public function delete(string $caseUuid, string $messageUuid): void
    {
        try {
            EloquentMessage::query()
                ->where('case_uuid', $caseUuid)
                ->where('uuid', $messageUuid)
                ->delete();
        } catch (Throwable $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function getByUuid(string $uuid): EloquentMessage
    {
        try {
            $builder = EloquentMessage::where('uuid', $uuid);
            return $builder->firstOrFail();
        } catch (Throwable $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
