<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Controllers\Controller;
use App\Http\Responses\Api\EloquentMessage\AttachmentDecorator;
use App\Http\Responses\Api\EloquentMessage\EloquentMessageDecorator;
use App\Http\Responses\Api\EloquentMessage\EloquentMessageSummaryDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\Attachment;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Services\CaseFragmentService;
use App\Services\ContextService;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;

use function is_string;

final class ApiCaseMessageController extends Controller
{
    use ValidatesModels;

    public function __construct(
        private readonly ContextService $contextService,
        private readonly CaseFragmentService $caseFragmentService,
        private readonly MessageService $messageService,
    ) {
    }

    #[SetAuditEventDescription('Case berichten opgehaald')]
    public function getMessages(
        EloquentCase $case,
        Request $request,
        AuditEvent $auditEvent,
        EloquentMessageSummaryDecorator $eloquentMessageSummaryDecorator,
    ): EncodableResponse {
        $messages = $this->getMessagesForRequest($case->uuid, $request);

        $auditEvent->objects(
            AuditObject::createArray(
                $messages->all(),
                static fn (EloquentMessage $eloquentMessage) => AuditObject::create('message', $eloquentMessage->uuid)
            ),
        );

        return
            EncodableResponseBuilder::create(['messages' => $messages])
            ->withContext(static function (EncodingContext $context) use ($eloquentMessageSummaryDecorator): void {
                    $context->registerDecorator(EloquentMessage::class, $eloquentMessageSummaryDecorator);
            })
                ->build();
    }

    public function getMessage(
        EloquentCase $case,
        EloquentMessage $message,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $auditEvent->object(AuditObject::create('message', $message->uuid));

        return EncodableResponseBuilder::create($message)
            ->withContext(static function (EncodingContext $context): void {
                $context->registerDecorator(EloquentMessage::class, new EloquentMessageDecorator());
                $context->registerDecorator(Attachment::class, new AttachmentDecorator());
            })
            ->build();
    }

    /**
     * @return Collection<EloquentMessage>
     */
    private function getMessagesForRequest(string $caseUuid, Request $request): Collection
    {
        if ($request->query('only_for_index')) {
            return $this->messageService->getCaseMessages($caseUuid);
        }

        $taskUuid = $request->query('contact_uuid');
        if (is_string($taskUuid)) {
            return $this->messageService->getTaskMessages($caseUuid, $taskUuid);
        }

        return $this->messageService->getMessages($caseUuid);
    }
}
