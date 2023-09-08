<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\RepositoryException;
use App\Models\Eloquent\EloquentMessage;
use Illuminate\Support\Collection;

interface MessageRepository
{
    /**
     * @return Collection<EloquentMessage>
     */
    public function findByCaseUuid(string $caseUuid): Collection;

    /**
     * @return Collection<EloquentMessage>
     */
    public function findByCaseUuidAndTaskUuid(string $caseUuid, ?string $taskUuid): Collection;

    /**
     * @throws RepositoryException
     */
    public function delete(string $caseUuid, string $messageUuid): void;

    /**
     * @throws RepositoryException
     */
    public function getByUuid(string $uuid): EloquentMessage;

    public function save(EloquentMessage $eloquentMessage): void;
}
