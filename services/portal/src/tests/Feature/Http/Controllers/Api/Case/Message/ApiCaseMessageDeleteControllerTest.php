<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\Message;

use App\Services\Message\MessageTransportService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\CaseFragmentGenerator;

use function sprintf;

#[Group('message')]
#[Group('case-message')]
#[Group('case')]
class ApiCaseMessageDeleteControllerTest extends FeatureTestCase
{
    use CaseFragmentGenerator;

    public function testDeleteWhenNotificationSent(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);
        $message = $this->createMessageForCase($case, [
            'notification_sent_at' => $this->faker->dateTime(),
        ]);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->deleteJson(
            sprintf('/api/cases/%s/messages/%s', $message->case_uuid, $message->uuid),
        );
        $response->assertStatus(204);
        $this->assertSoftDeleted('message', [
            'uuid' => $message->uuid,
        ]);
        $this->assertDatabaseHas('message', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testDeleteWhenNotificationNotSent(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);
        $message = $this->createMessageForCase($case, [
            'notification_sent_at' => null,
        ]);

        $response = $this->be($user)->deleteJson(
            sprintf('/api/cases/%s/messages/%s', $message->case_uuid, $message->uuid),
        );
        $response->assertStatus(204);
        $this->assertSoftDeleted('message', [
            'uuid' => $message->uuid,
        ]);
    }
}
