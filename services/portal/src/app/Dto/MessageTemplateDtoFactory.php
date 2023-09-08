<?php

declare(strict_types=1);

namespace App\Dto;

use App\Exceptions\MessageException;
use App\Helpers\MessageTemplateTypeConfigHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Services\EmailLanguageService;
use App\Services\Message\EmailTemplateHelper;
use App\Services\Message\MessageTextRenderer;
use Illuminate\Contracts\Translation\Translator;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Psr\Container\ContainerExceptionInterface;

use function sprintf;

class MessageTemplateDtoFactory
{
    public function __construct(
        private readonly Translator $translator,
        private readonly EmailLanguageService $emailLanguageService,
        private readonly MessageTextRenderer $messageTextRenderer,
    ) {
    }

    /**
     * @throws MessageException
     */
    public function create(
        MessageTemplateType $messageTemplateType,
        EloquentCase $eloquentCase,
        ?EloquentTask $eloquentTask,
        array $attachments,
    ): MessageTemplateDto {
        $this->validate($messageTemplateType);

        $messageTemplateConfig = MessageTemplateTypeConfigHelper::getConfig($messageTemplateType);

        $preferredEmailLanguage = $this->emailLanguageService->getByCaseOrTask($eloquentCase, $eloquentTask);
        $emailLanguage = EmailTemplateHelper::validateEmailLanguageOrFallbackToDefault(
            $preferredEmailLanguage,
            $messageTemplateConfig['template'],
        );

        try {
            $subject = $this->translator->get(
                sprintf('mail-template-subject.%s', $messageTemplateConfig['template']),
                [],
                $emailLanguage->value,
            );
        } catch (ContainerExceptionInterface $containerException) {
            throw MessageException::fromThrowable($containerException);
        }

        return new MessageTemplateDto(
            $subject,
            $this->messageTextRenderer->createText(
                $emailLanguage,
                $messageTemplateConfig['template'],
                $messageTemplateConfig['secure'],
                $eloquentCase,
                $eloquentTask,
                null,
            ),
            $messageTemplateConfig['secure'],
            $emailLanguage->value,
            $attachments,
        );
    }

    /**
     * @throws MessageException
     */
    public function validate(MessageTemplateType $messageTemplateType): void
    {
        if (MessageTemplateTypeConfigHelper::isDisabled($messageTemplateType)) {
            throw new MessageException(sprintf('template %s is disabled by feature-flag', $messageTemplateType->value));
        }
    }
}
