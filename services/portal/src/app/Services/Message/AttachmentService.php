<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\AttachmentException;
use App\Models\Eloquent\Attachment;
use App\Repositories\AttachmentRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Throwable;
use Webmozart\Assert\Assert;

use function base64_encode;
use function config;
use function is_string;
use function sort;
use function sprintf;

class AttachmentService
{
    public function __construct(
        private readonly AttachmentRepository $attachmentRepository,
    ) {
    }

    public function getAttachment(string $uuid): Attachment
    {
        $attachment = $this->attachmentRepository->getAttachment($uuid);

        AttachmentFileHelper::validate($attachment);
        Assert::notNull($attachment);

        return $attachment;
    }

    public function getAttachmentByName(string $fileName): Attachment
    {
        $attachment = $this->attachmentRepository->getAttachmentByName($fileName);

        AttachmentFileHelper::validate($attachment);
        Assert::notNull($attachment);

        return $attachment;
    }

    /**
     * @return array{filename: string, content: string, mime_type: string}
     *
     * @throws AttachmentException
     */
    public function convertToArray(Attachment $attachment): array
    {
        return [
            'filename' => $attachment->file_name,
            'content' => base64_encode(AttachmentFileHelper::getContent($attachment)),
            'mime_type' => AttachmentFileHelper::getMimeType($attachment),
        ];
    }

    public function getAttachmentsForTemplateType(?MessageTemplateType $messageTemplateType): array
    {
        if ($messageTemplateType === null) {
            return [];
        }

        $availableAttachmentsConfig = config(
            sprintf('messagetemplate.%s.attachments', Str::snake($messageTemplateType->value)),
        );

        try {
            Assert::isIterable($availableAttachmentsConfig);
        } catch (InvalidArgumentException) {
            return [];
        }

        $availableAttachments = [];
        foreach ($availableAttachmentsConfig as $availableAttachmentFilename) {
            if (!is_string($availableAttachmentFilename)) {
                continue;
            }

            try {
                $availableAttachment = $this->getAttachmentByName($availableAttachmentFilename);
                $availableAttachments[] = [
                    'uuid' => $availableAttachment->uuid,
                    'filename' => $availableAttachment->file_name,
                ];
            } catch (Throwable $e) {
                Log::error(sprintf('%s: %s', $e->getMessage(), $availableAttachmentFilename));
            }
        }
        sort($availableAttachments);

        return $availableAttachments;
    }
}
