<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\EloquentMessage;

use App\Dto\MessageTemplateDto;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class EloquentMessageTemplateEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof MessageTemplateDto);

        $container->subject = $value->getSubject();
        $container->body = $value->getText();
        $container->isSecure = $value->isSecure();
        $container->language = $value->getMailLanguage();
        $container->attachments = $value->getAttachments();
    }
}
