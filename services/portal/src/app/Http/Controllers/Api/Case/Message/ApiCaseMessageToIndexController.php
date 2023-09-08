<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\Message;

use App\Exceptions\MessageException;
use App\Exceptions\MessageTemplateTypeException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\CaseMessage\SendMessageToIndexRequest;
use App\Http\Responses\Api\EloquentMessage\EloquentMessageDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Services\AuthenticationService;
use App\Services\Message\MessageFactoryService;
use App\Services\Message\MessageTransportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

class ApiCaseMessageToIndexController extends ApiController
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly MessageFactoryService $messageFactoryService,
        private readonly MessageTransportService $messageTransportService,
    ) {
    }

    #[SetAuditEventDescription('Verzend bericht naar index')]
    public function send(
        EloquentCase $eloquentCase,
        SendMessageToIndexRequest $request,
        AuditEvent $auditEvent,
    ): EncodableResponse|JsonResponse {
        $auditEvent->object(AuditObject::create('message'));

        try {
            $eloquentMessage = DB::transaction(function () use ($request, $eloquentCase): EloquentMessage {
                $eloquentMessage = $this->messageFactoryService->create(
                    $this->authenticationService->getAuthenticatedUser(),
                    $request->getMessageTemplateType(),
                    $eloquentCase,
                    null,
                    $request->getInputAddedText(),
                    $request->getAttachments(),
                );
                $this->messageTransportService->send($eloquentMessage);

                return $eloquentMessage;
            });
        } catch (MessageException | MessageTemplateTypeException $exception) {
            return $this->createErrorResponseFromException($exception, 'send message to index failed');
        }

        return EncodableResponseBuilder::create($eloquentMessage)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(EloquentMessage::class, new EloquentMessageDecorator());
            })
            ->build();
    }
}
