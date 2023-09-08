<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message\Transport;

use App\Exceptions\MessageException;
use App\Mail\Message;
use App\Services\Message\Transport\SmtpMailerMessageTransport;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\FeatureTestCase;

class SmtpMailerMessageTransportTest extends FeatureTestCase
{
    public function testSend(): void
    {
        Mail::fake();

        $eloquentMessage = $this->createMessage([
            'is_secure' => false,
        ]);

        $smtpMailerMessageTransport = $this->app->get(SmtpMailerMessageTransport::class);
        $returnedMailerIdentifier = $smtpMailerMessageTransport->send($eloquentMessage);
        $this->assertNull($returnedMailerIdentifier);

        Mail::assertSent(static function (Message $message) use ($eloquentMessage): bool {
            $message->build();

            return $message->hasTo($eloquentMessage->to_email, $eloquentMessage->to_name)
                && $message->subject === $eloquentMessage->subject;
        });
    }

    public function testSendBlockedForSecureMessage(): void
    {
        Mail::fake();

        $eloquentMessage = $this->createMessage([
            'is_secure' => true,
        ]);

        $smtpMailerMessageTransport = $this->app->get(SmtpMailerMessageTransport::class);

        $this->expectException(MessageException::class);
        $smtpMailerMessageTransport->send($eloquentMessage);
    }
}
