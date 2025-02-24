<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\EloquentMessage;

use App\Models\Eloquent\EloquentMessage;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class EloquentMessageSummaryDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof EloquentMessage);

        $container->uuid = $value->uuid;
        $container->mailVariant = $value->message_template_type;
        $container->caseUuid = $value->case_uuid;
        $container->taskUuid = $value->task_uuid;
        $container->toEmail = $value->to_email;
        $container->toName = $value->to_name;
        $container->telephone = $value->telephone;
        $container->subject = $value->subject;
        $container->createdAt = $value->created_at;
        $container->expiresAt = $value->expires_at;
        $container->notificationSentAt = $value->notification_sent_at;
        $container->status = $value->status;
        $container->isExpired = $value->expires_at?->isPast() === true;
        $container->isDeleted = $value->deleted_at?->isPast() === true;
        $container->identityRequired = $value->identity_required;
        $container->isIdentified = $value->pseudo_bsn !== null;
        $container->hasAttachments = !$value->attachments->isEmpty();
    }
}
