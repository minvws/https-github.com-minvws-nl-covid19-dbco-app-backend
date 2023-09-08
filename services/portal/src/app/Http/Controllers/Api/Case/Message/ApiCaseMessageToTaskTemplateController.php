<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\Message;

use App\Dto\MessageTemplateDto;
use App\Dto\MessageTemplateDtoFactory;
use App\Exceptions\MessageException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Responses\Api\EloquentMessage\EloquentMessageTemplateEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Services\Message\AttachmentService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

class ApiCaseMessageToTaskTemplateController extends ApiController
{
    private AttachmentService $attachmentService;
    private MessageTemplateDtoFactory $messageTemplateDtoFactory;

    public function __construct(
        AttachmentService $attachmentService,
        MessageTemplateDtoFactory $messageTemplateDtoFactory,
    ) {
        $this->attachmentService = $attachmentService;
        $this->messageTemplateDtoFactory = $messageTemplateDtoFactory;
    }

    /**
     * @throws AuthenticationException
     */
    #[SetAuditEventDescription('Bericht template voor contact opgehaald')]
    public function get(
        EloquentCase $eloquentCase,
        MessageTemplateType $messageTemplateType,
        EloquentTask $eloquentTask,
    ): EncodableResponse|JsonResponse {
        try {
            $messageTemplateDto = $this->messageTemplateDtoFactory->create(
                $messageTemplateType,
                $eloquentCase,
                $eloquentTask,
                $this->attachmentService->getAttachmentsForTemplateType($messageTemplateType),
            );
        } catch (MessageException $messageException) {
            return $this->createErrorResponseFromException($messageException, 'message template for task failed');
        }

        return EncodableResponseBuilder::create($messageTemplateDto)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(MessageTemplateDto::class, new EloquentMessageTemplateEncoder());
            })
            ->build();
    }
}
