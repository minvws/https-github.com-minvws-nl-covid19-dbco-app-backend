<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message\Transport;

use App\Mail\Message;
use App\Services\Message\Transport\ZivverMessageTransport;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\FeatureTestCase;

class ZivverMessageTransportTest extends FeatureTestCase
{
    public function testSend(): void
    {
        Mail::fake();

        $eloquentMessage = $this->createMessage([
            'is_secure' => false,
        ]);

        $zivverMessageTransport = $this->app->get(ZivverMessageTransport::class);
        $returnedMailerIdentifier = $zivverMessageTransport->send($eloquentMessage);
        $this->assertNull($returnedMailerIdentifier);

        Mail::assertSent(static function (Message $message) use ($eloquentMessage): bool {
            $message->build();

            if (!$message->hasTo($eloquentMessage->to_email, $eloquentMessage->to_name)) {
                return false;
            }

            if ($message->subject !== $eloquentMessage->subject) {
                return false;
            }

            // there is currently no way to check the proper headers (zivver-access-right & zivver-message-expiration),
            // therefore just make sure there is *a* callback that *should* set these headers
            return !empty($message->callbacks);
        });
    }
}
