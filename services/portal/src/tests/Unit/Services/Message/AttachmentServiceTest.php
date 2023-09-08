<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Message;

use App\Exceptions\AttachmentFileNotFoundHttpException;
use App\Exceptions\AttachmentNotFoundHttpException;
use App\Models\Eloquent\Attachment;
use App\Repositories\AttachmentRepository;
use App\Services\Message\AttachmentService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function app;

class AttachmentServiceTest extends TestCase
{
    #[Group('message-attachment')]
    public function testGetMessageTemplateAttachmentNotFoundForIndex(): void
    {
        $attachmentRecordNotFound = 'Attachment record not found.pdf';

        $this->mock(
            AttachmentRepository::class,
            static function (MockInterface $mock) use ($attachmentRecordNotFound): void {
                $mock->expects('getAttachmentByName')
                    ->with($attachmentRecordNotFound)
                    ->andReturnNull();
            },
        );

        $this->expectException(AttachmentNotFoundHttpException::class);

        /** @var AttachmentService $attachmentService */
        $attachmentService = app(AttachmentService::class);
        $attachmentService->getAttachmentByName($attachmentRecordNotFound);
    }

    #[Group('message-attachment')]
    public function testGetMessageTemplateAttachmentFileNotFoundForIndex(): void
    {
        $attachmentFileNotFound = 'Attachment file not found.pdf';
        $attachment = new Attachment();
        $attachment->file_name = $attachmentFileNotFound;

        $this->mock(
            AttachmentRepository::class,
            static function (MockInterface $mock) use ($attachmentFileNotFound, $attachment): void {
                $mock->expects('getAttachmentByName')
                    ->with($attachmentFileNotFound)
                    ->andReturn($attachment);
            },
        );

        $this->expectException(AttachmentFileNotFoundHttpException::class);

        /** @var AttachmentService $attachmentService */
        $attachmentService = app(AttachmentService::class);
        $attachmentService->getAttachmentByName($attachmentFileNotFound);
    }

    #[Group('message-attachment')]
    public function testGetAttachmentsForTemplateWithNull(): void
    {
        /** @var AttachmentService $attachmentService */
        $attachmentService = app(AttachmentService::class);
        $this->assertEmpty($attachmentService->getAttachmentsForTemplateType(null));
    }
}
