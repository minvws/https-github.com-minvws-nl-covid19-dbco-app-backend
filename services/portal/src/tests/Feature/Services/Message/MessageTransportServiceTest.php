<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message;

use App\Services\Message\MessageTransportService;
use App\Services\Message\Transport\SecureMailMessageTransport;
use App\Services\Message\Transport\SmtpMailerMessageTransport;
use App\Services\Message\Transport\ZivverMessageTransport;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function config;

#[Group('secure-mail-send-message')]
class MessageTransportServiceTest extends FeatureTestCase
{
    private MessageTransportService $messageTransportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageTransportService = $this->app->get(MessageTransportService::class);
    }

    #[DataProvider('transportDataProvider')]
    public function testSend(string $transport, string $abstract): void
    {
        config()->set('messagetemplate.contact_infection', ['transport' => $transport]);
        $now = CarbonImmutable::createStrict(2020);
        CarbonImmutable::setTestNow($now);

        $message = $this->createMessage([
            'message_template_type' => MessageTemplateType::contactInfection(),
            'mailer_identifier' => null,
            'notification_sent_at' => null,
        ]);

        $mailerIdentifier = $this->faker->optional()->uuid();

        $this->mock($abstract, static function (MockInterface $mock) use ($message, $mailerIdentifier): void {
            $mock->expects('send')
                ->with($message)
                ->andReturn($mailerIdentifier);
        });

        $this->messageTransportService->send($message);

        $message->refresh();
        $this->assertEquals($mailerIdentifier, $message->mailer_identifier);
        $this->assertEquals($now, $message->notification_sent_at);
    }

    public static function transportDataProvider(): array
    {
        return [
            'secure_mail' => ['secure_mail', SecureMailMessageTransport::class],
            'smtp' => ['smtp', SmtpMailerMessageTransport::class],
            'zivver' => ['zivver', ZivverMessageTransport::class],
        ];
    }

    public function testNonExistingDriver(): void
    {
        config()->set('messagetemplate.contact_infection', ['transport' => 'nonexisting']);

        $message = $this->createMessage([
            'message_template_type' => MessageTemplateType::contactInfection(),
        ]);

        $this->expectExceptionMessage('Driver [nonexisting] not supported.');
        $this->messageTransportService->send($message);
    }
}
