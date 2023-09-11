<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\EloquentMessage;

use App\Models\Eloquent\Attachment;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class AttachmentDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Attachment);

        $container->uuid = $value->uuid;
        $container->fileName = $value->file_name;
        $container->createdAt = $value->created_at;
        $container->updatedAt = $value->updated_at;
        $container->inactiveSince = $value->inactive_since;
    }
}
