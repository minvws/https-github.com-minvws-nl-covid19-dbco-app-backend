<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message;

use App\Exceptions\RepositoryException;
use App\Models\Eloquent\EloquentMessage;
use App\Repositories\MessageRepository;
use App\Services\Message\MessageUpdateService;
use App\Services\SecureMail\SecureMailStatusUpdate;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\MessageStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\FeatureTestCase;

class MessageUpdateSerivceTest extends FeatureTestCase
{
    public function testUpdateMessages(): void
    {
        $message = $this->createMessage();
        $notificationSentAt = CarbonImmutable::instance($this->faker->dateTime());
        $messageStatus = $this->faker->randomElement(MessageStatus::all());

        /** @var MessageRepository&MockObject $messageRepository */
        $messageRepository = $this->mock(
            MessageRepository::class,
            static function (MockInterface $mock) use ($message, $notificationSentAt, $messageStatus): void {
                $mock->expects('getByUuid')
                    ->with($message->uuid)
                    ->andReturn($message);

                $mock->expects('save')
                    ->withArgs(
                        static function (EloquentMessage $message) use ($notificationSentAt, $messageStatus): bool {
                            return $message->notification_sent_at->equalTo($notificationSentAt)
                                && $message->status === $messageStatus;
                        },
                    );
            },
        );

        $messageUpdateService = new MessageUpdateService($messageRepository);
        $messageUpdateService->updateMessages([
            new SecureMailStatusUpdate(
                $message->uuid,
                $notificationSentAt,
                $messageStatus,
            ),
        ]);
    }

    public function testUpdateNonExisting(): void
    {
        $messageUuid = $this->faker->uuid();

        /** @var MessageRepository&MockObject $messageRepository */
        $messageRepository = $this->mock(
            MessageRepository::class,
            static function (MockInterface $mock) use ($messageUuid): void {
                $mock->expects('getByUuid')
                    ->with($messageUuid)
                    ->andThrow(new RepositoryException('message not found'));
            },
        );

        $messageUpdateService = new MessageUpdateService($messageRepository);
        $updateMessageCount = $messageUpdateService->updateMessages([
            new SecureMailStatusUpdate(
                $messageUuid,
                CarbonImmutable::instance($this->faker->dateTime()),
                $this->faker->randomElement(MessageStatus::all()),
            ),
        ]);

        $this->assertEquals(0, $updateMessageCount);
    }
}
