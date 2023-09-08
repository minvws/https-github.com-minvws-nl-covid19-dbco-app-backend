<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message;

use App\Models\Eloquent\Attachment;
use App\Repositories\AttachmentRepository;
use App\Services\Message\AttachmentService;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function config;

class AttachmentServiceTest extends FeatureTestCase
{
    private const ATTACHMENT_TEST_FILENAME = '20221004_Bijlage_Vaccineren_Index_en_COVID-19.pdf';

    #[Group('message-attachment')]
    public function testGetAttachmentForTemplateTypeAttachmentNotFoundShouldNotFail(): void
    {
        $attachmentRecordNotFound = self::ATTACHMENT_TEST_FILENAME;
        config()->set('messagetemplate.missed_phone.attachments', [self::ATTACHMENT_TEST_FILENAME]);

        $this->mock(
            AttachmentRepository::class,
            static function (MockInterface $mock) use ($attachmentRecordNotFound): void {
                $mock->expects('getAttachmentByName')
                    ->with($attachmentRecordNotFound)
                    ->andReturnNull();
            },
        );

        /** @var AttachmentService $attachmentService */
        $attachmentService = $this->app->get(AttachmentService::class);

        //No attachments in database so we should find an empty result
        $this->assertEmpty($attachmentService->getAttachmentsForTemplateType(MessageTemplateType::missedPhone()));
    }

    #[Group('message-attachment')]
    public function testGetAttachment(): void
    {
        $attachmentUuid = $this->faker->uuid();
        $this->createAttachment([
            'uuid' => $attachmentUuid,
            'file_name' => self::ATTACHMENT_TEST_FILENAME,
        ]);

        /** @var AttachmentService $attachmentService */
        $attachmentService = app(AttachmentService::class);

        $this->assertInstanceOf(Attachment::class, $attachmentService->getAttachment($attachmentUuid));
    }
}
