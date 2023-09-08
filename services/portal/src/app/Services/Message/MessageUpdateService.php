<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\RepositoryException;
use App\Repositories\MessageRepository;
use App\Services\SecureMail\SecureMailStatusUpdate;

readonly class MessageUpdateService
{
    public function __construct(
        private MessageRepository $messageRepository,
    ) {
    }

    /**
     * @param array<SecureMailStatusUpdate> $secureMailStatusUpdates
     */
    public function updateMessages(array $secureMailStatusUpdates): int
    {
        $updateCount = 0;

        foreach ($secureMailStatusUpdates as $secureMailStatusUpdate) {
            try {
                $this->updateMessage($secureMailStatusUpdate);
            } catch (RepositoryException) {
                continue;
            }

            $updateCount++;
        }

        return $updateCount;
    }

    /**
     * @throws RepositoryException
     */
    private function updateMessage(SecureMailStatusUpdate $secureMailStatusUpdate): void
    {
        $message = $this->messageRepository->getByUuid($secureMailStatusUpdate->id);

        $message->notification_sent_at = $secureMailStatusUpdate->notificationSentAt;
        $message->status = $secureMailStatusUpdate->status;

        $this->messageRepository->save($message);
    }
}
