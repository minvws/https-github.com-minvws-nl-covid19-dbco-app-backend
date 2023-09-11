<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\Message;

use App\Exceptions\MessageException;
use App\Exceptions\RepositoryException;
use App\Http\Controllers\Api\ApiController;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Repositories\MessageRepository;
use App\Services\AuthenticationService;
use App\Services\Message\MessageFactoryService;
use App\Services\Message\MessageTransportService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

use function response;

class ApiCaseMessageDeleteController extends ApiController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly MessageFactoryService $messageFactoryService,
        private readonly MessageRepository $messageRepository,
        private readonly MessageTransportService $transportService,
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    #[SetAuditEventDescription('Bericht verwijderen')]
    public function send(
        EloquentCase $eloquentCase,
        EloquentMessage $eloquentMessage,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $auditEvent->object(AuditObject::create('message'));

        try {
            $this->messageRepository->delete($eloquentCase->uuid, $eloquentMessage->uuid);
        } catch (RepositoryException $repositoryException) {
            return $this->createErrorResponseFromException($repositoryException, 'delete message failed');
        }

        if ($eloquentMessage->notification_sent_at !== null) {
            try {
                $confirmationEloquentMessage = $this->messageFactoryService->create(
                    $this->authenticationService->getAuthenticatedUser(),
                    MessageTemplateType::deletedMessage(),
                    $eloquentCase,
                    additionalViewData: [
                        'deletedMessageDate' => $eloquentMessage->notification_sent_at,
                    ],
                );

                $this->transportService->send($confirmationEloquentMessage);
            } catch (MessageException $messageException) {
                return $this->createErrorResponseFromException($messageException, 'send delete-message failed');
            }
        }

        return response()->json()->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
