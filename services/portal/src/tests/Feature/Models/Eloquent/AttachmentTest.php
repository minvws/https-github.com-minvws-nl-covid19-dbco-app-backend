<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use DateTimeInterface;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

class AttachmentTest extends FeatureTestCase
{
    public function testCreateAttachment(): void
    {
        $attachment = $this->createAttachment();

        $this->assertTrue(Uuid::isValid($attachment->uuid));
        $this->assertDatabaseHas('attachment', ['uuid' => $attachment->uuid]);
    }

    public function testAttachmentInactiveSince(): void
    {
        $attachment = $this->createAttachment([
            'inactive_since' => $this->faker->dateTime(),
        ]);

        $this->assertInstanceOf(DateTimeInterface::class, $attachment->inactive_since);
    }

    public function testAttachAttachmentToMessage(): void
    {
        $message = $this->createMessage();
        $attachment = $this->createAttachment();

        $message->attachments()->attach($attachment);

        $this->assertEquals($attachment->file_name, $message->attachments()->first()->fileName);
    }
}
