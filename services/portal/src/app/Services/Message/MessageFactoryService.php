<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\MessageException;
use App\Helpers\Config;
use App\Helpers\MessageTemplateTypeConfigHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Repositories\MessageRepository;
use App\Services\EmailLanguageService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Translation\Translator;
use MinVWS\DBCO\Enum\Models\MessageStatus;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Ramsey\Uuid\Uuid;

use function count;
use function is_int;
use function sprintf;

class MessageFactoryService
{
    public function __construct(
        private readonly EmailLanguageService $emailLanguageService,
        private readonly Translator $translator,
        private readonly MessageRepository $messageRepository,
        private readonly MessageTextRenderer $messageTextRenderer,
    ) {
    }

    /**
     * @param array<string> $attachmentUuids
     *
     * @throws MessageException
     */
    public function create(
        EloquentUser $eloquentUser,
        MessageTemplateType $messageTemplateType,
        EloquentCase $eloquentCase,
        ?EloquentTask $eloquentTask = null,
        ?string $additionalAdvice = null,
        array $attachmentUuids = [],
        array $additionalViewData = [],
    ): EloquentMessage {
        $this->validate($messageTemplateType, $eloquentCase, $eloquentTask);

        $messageTemplateConfig = MessageTemplateTypeConfigHelper::getConfig($messageTemplateType);
        $preferredEmailLanguage = $this->emailLanguageService->getByCaseOrTask($eloquentCase, $eloquentTask);

        $emailLanguage = EmailTemplateHelper::validateEmailLanguageOrFallbackToDefault(
            $preferredEmailLanguage,
            $messageTemplateConfig['template'],
        );

        if ($eloquentTask === null) {
            $emailAddress = $eloquentCase->contact->email;
            $name = empty($eloquentCase->name) ? $emailAddress : $eloquentCase->name;
            $pseudoBsn = $eloquentCase->pseudo_bsn_guid;
            $phoneNumber = $eloquentCase->contact->phone;
        } else {
            $emailAddress = $eloquentTask->general->email;
            $name = empty($eloquentTask->name) ? $emailAddress : $eloquentTask->name;
            $pseudoBsn = $eloquentTask->pseudo_bsn_guid;
            $phoneNumber = $eloquentTask->general->phone;
        }

        $subject = $this->translator->get(
            sprintf('mail-template-subject.%s', $messageTemplateConfig['template']),
            [],
            $emailLanguage->value,
        );

        $eloquentMessage = new EloquentMessage();
        $eloquentMessage->uuid = Uuid::uuid4()->toString();
        $eloquentMessage->case_created_at = $eloquentCase->created_at;
        $eloquentMessage->user_uuid = $eloquentUser->uuid;
        $eloquentMessage->case_uuid = $eloquentCase->uuid;
        $eloquentMessage->task_uuid = $eloquentTask?->uuid;
        $eloquentMessage->message_template_type = $messageTemplateType;
        $eloquentMessage->mail_template = $messageTemplateConfig['template'];
        $eloquentMessage->mail_language = $emailLanguage;
        $eloquentMessage->from_email = Config::string('mail.from.address');
        $eloquentMessage->from_name = $eloquentCase->organisation->name;
        $eloquentMessage->to_email = $emailAddress;
        $eloquentMessage->to_name = $name;
        $eloquentMessage->telephone = $phoneNumber;
        $eloquentMessage->pseudo_bsn = $pseudoBsn;
        $eloquentMessage->subject = $subject;
        $eloquentMessage->text = $this->messageTextRenderer->createText(
            $emailLanguage,
            $messageTemplateConfig['template'],
            $messageTemplateConfig['secure'],
            $eloquentCase,
            $eloquentTask,
            $additionalAdvice,
            $additionalViewData,
        );
        $eloquentMessage->status = MessageStatus::draft();
        $eloquentMessage->identity_required = $messageTemplateConfig['identity_required'];
        $eloquentMessage->is_secure = $messageTemplateConfig['secure'];

        if (is_int($messageTemplateConfig['expiry_days'])) {
            $eloquentMessage->expires_at = CarbonImmutable::now()->addDays($messageTemplateConfig['expiry_days']);
        }

        $this->messageRepository->save($eloquentMessage);

        $this->addAttachments($attachmentUuids, $eloquentMessage);

        return $eloquentMessage;
    }

    /**
     * @throws MessageException
     */
    public function validate(
        MessageTemplateType $messageTemplateType,
        EloquentCase $eloquentCase,
        ?EloquentTask $eloquentTask,
    ): void {
        if (MessageTemplateTypeConfigHelper::isDisabled($messageTemplateType)) {
            throw new MessageException(sprintf('template %s is disabled by feature-flag', $messageTemplateType->value));
        }

        if ($eloquentCase->organisation === null) {
            throw new MessageException('case is not attached to an organisation');
        }

        if ($eloquentTask?->general?->email === null && $eloquentCase->contact->email === null) {
            throw new MessageException('emailaddress is not set');
        }
    }

    /**
     * @param array<string> $attachmentUuids
     */
    private function addAttachments(array $attachmentUuids, EloquentMessage $eloquentMessage): void
    {
        if (count($attachmentUuids) > 0) {
            $eloquentMessage->attachments()->sync($attachmentUuids);
        }
    }
}
