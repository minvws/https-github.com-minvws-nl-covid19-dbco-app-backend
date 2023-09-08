<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Exceptions\AttachmentException;
use App\Exceptions\AttachmentFileNotFoundHttpException;
use App\Exceptions\AttachmentNotFoundHttpException;
use App\Models\Eloquent\Attachment;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Throwable;

use function sprintf;

use const DIRECTORY_SEPARATOR;

class AttachmentFileHelper
{
    public const FILESYSTEM_DISK_ATTACHMENTS = 'attachments';

    /**
     * @throws AttachmentFileNotFoundHttpException
     * @throws AttachmentNotFoundHttpException
     */
    public static function validate(?Attachment $attachment): void
    {
        if ($attachment === null || $attachment->file_name === null) {
            throw new AttachmentNotFoundHttpException();
        }

        self::exists($attachment);
    }

    /**
     * @throws AttachmentFileNotFoundHttpException
     */
    public static function exists(Attachment $attachment): bool
    {
        if (!self::getStorage()->exists(self::getPath($attachment))) {
            throw new AttachmentFileNotFoundHttpException();
        }

        return true;
    }

    /**
     * @throws AttachmentFileNotFoundHttpException
     */
    public static function getContent(Attachment $attachment): string
    {
        self::exists($attachment);

        try {
            return self::getStorage()->get(self::getPath($attachment));
        } catch (FileNotFoundException $fileNotFoundException) {
            throw new AttachmentFileNotFoundHttpException($fileNotFoundException);
        }
    }

    /**
     * @throws AttachmentException
     * @throws AttachmentFileNotFoundHttpException
     */
    public static function getMimeType(Attachment $attachment): string
    {
        try {
            $mimeType = self::getStorage()->mimeType(self::getPath($attachment));
            if ($mimeType === false) {
                throw new AttachmentException('unable to read mime-type');
            }

            return $mimeType;
        } catch (Throwable) {
            throw new AttachmentFileNotFoundHttpException();
        }
    }

    public static function getPath(Attachment $attachment): string
    {
        return sprintf('%s%s', DIRECTORY_SEPARATOR, $attachment->file_name);
    }

    private static function getStorage(): Filesystem
    {
        return Storage::disk(self::FILESYSTEM_DISK_ATTACHMENTS);
    }
}
