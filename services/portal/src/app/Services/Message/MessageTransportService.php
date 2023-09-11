<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\MessageException;
use App\Models\Eloquent\EloquentMessage;
use App\Repositories\MessageRepository;
use App\Services\Message\Transport\MessageTransport;
use App\Services\Message\Transport\MessageTransportManager;
use Carbon\CarbonImmutable;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;

use function sprintf;

class MessageTransportService
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly MessageTransportManager $messageTransportManager,
        private readonly MessageRepository $messageRepository,
    ) {
    }

    /**
     * @throws MessageException
     */
    public function send(EloquentMessage $eloquentMessage): void
    {
        $messageTemplateType = Str::snake($eloquentMessage->message_template_type->value);
        $messageTemplateConfig = $this->config->get(sprintf('messagetemplate.%s', $messageTemplateType));

        /** @var MessageTransport $messageTransport */
        $messageTransport = $this->messageTransportManager->driver($messageTemplateConfig['transport']);
        $eloquentMessage->mailer_identifier = $messageTransport->send($eloquentMessage);

        $eloquentMessage->notification_sent_at = CarbonImmutable::now();

        $this->messageRepository->save($eloquentMessage);
    }
}
