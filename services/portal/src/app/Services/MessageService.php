<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RepositoryException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Repositories\MessageRepository;
use Illuminate\Support\Collection;

final class MessageService
{
    private MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function getMessageByUuid(string $messageUuid): ?EloquentMessage
    {
        try {
            return $this->messageRepository->getByUuid($messageUuid);
        } catch (RepositoryException) {
            return null;
        }
    }

    /**
     * @return Collection<EloquentMessage>
     */
    public function getCaseMessages(string $caseUuid): Collection
    {
        return $this->messageRepository->findByCaseUuidAndTaskUuid($caseUuid, null);
    }

    /**
     * @return Collection<EloquentMessage>
     */
    public function getTaskMessages(string $caseUuid, string $taskUuid): Collection
    {
        return $this->messageRepository->findByCaseUuidAndTaskUuid($caseUuid, $taskUuid);
    }

    /**
     * @return Collection<EloquentMessage>
     */
    public function getMessages(string $caseUuid): Collection
    {
        return $this->messageRepository->findByCaseUuid($caseUuid);
    }

    /**
     * @throws RepositoryException
     */
    public function delete(EloquentCase $eloquentCase, EloquentMessage $eloquentMessage): void
    {
        $this->messageRepository->delete($eloquentCase->uuid, $eloquentMessage->uuid);
    }
}
