<?php

declare(strict_types=1);

namespace Tests\Unit\Message\Transport;

use App\Exceptions\AttachmentFileNotFoundHttpException;
use App\Exceptions\AttachmentNotFoundHttpException;
use App\Models\Eloquent\Attachment;
use App\Services\Message\AttachmentFileHelper;
use Generator;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AttachmentFileHelperTest extends TestCase
{
    private const ATTACHMENT_TEST_FILENAME = '20221004_Bijlage_Vaccineren_Index_en_COVID-19.pdf';

    #[DataProvider('validateAttachmentDataProvider')]
    public function testValidateFileNotFoundException(?Attachment $attachment): void
    {
        $this->expectException(AttachmentNotFoundHttpException::class);

        AttachmentFileHelper::validate($attachment);
    }

    public static function validateAttachmentDataProvider(): array
    {
        return [
            'Attachment null' => [null],
            'Attachment empty' => [new Attachment()],
        ];
    }

    public function testGetContentsFileNotFoundException(): void
    {
        $this->expectException(AttachmentFileNotFoundHttpException::class);

        $attachment = new Attachment();
        $attachment->file_name = $this->faker->slug();
        AttachmentFileHelper::getContent($attachment);
    }

    public function testGetContent(): void
    {
        $attachment = new Attachment();
        $attachment->file_name = self::ATTACHMENT_TEST_FILENAME;
        $content = AttachmentFileHelper::getContent($attachment);

        $expectedContent = Storage::disk(AttachmentFileHelper::FILESYSTEM_DISK_ATTACHMENTS)->get($attachment->file_name);

        $this->assertEquals($expectedContent, $content);
    }

    #[DataProvider('fileExistsDataProvider')]
    public function testFileExists(Attachment $attachment, bool $expectedException): void
    {
        if ($expectedException) {
            $this->expectException(AttachmentFileNotFoundHttpException::class);
        }
        $this->assertTrue(AttachmentFileHelper::exists($attachment));
    }

    public static function fileExistsDataProvider(): Generator
    {
        $attachment = new Attachment();
        $attachment->file_name = "a-file-that-does-not-exist";
        yield 'Attachment not exists' => [$attachment, true];

        $attachment = new Attachment();
        $attachment->file_name = self::ATTACHMENT_TEST_FILENAME;
        yield 'Attachment exists' => [$attachment, false];
    }
}
