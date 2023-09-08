<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\Message;

use App\Exceptions\MessageException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Services\Message\AttachmentService;
use App\Services\Message\MessageTransportService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;
use Tests\Traits\CaseFragmentGenerator;

use function array_map;
use function count;
use function sprintf;

#[Group('message')]
#[Group('case-message')]
#[Group('case')]
class ApiCaseMessageToTaskControllerTest extends FeatureTestCase
{
    use CaseFragmentGenerator;

    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('message', [
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    #[DataProvider('secureMailTaskNonSecureDoesNotContainGuidelinesDataProvider')]
    public function testSecureMailTaskNonSecureDoesNotContainGuidelines(?ContactCategory $contactCategory): void
    {
        ConfigHelper::set('messagetemplate.missed_phone.secure', false);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);
        $this->updateCaseWithAllFragments($case);
        $task->category = $contactCategory;
        $task->save();

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::missedPhone()->value,
        ]);

        $response->assertStatus(200);
    }

    public static function secureMailTaskNonSecureDoesNotContainGuidelinesDataProvider(): array
    {
        return [
            'none' => [null],
            'cat 1' => [ContactCategory::cat1()],
            'cat 2' => [ContactCategory::cat2a()],
        ];
    }

    public function testNonValidCaseUuid(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/nonexisting', $case->uuid));
        $response->assertStatus(404);
    }

    public function testMessageTemplateTypeIsRequired(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function testMessageTemplateTypeInvalid(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $response = $this->be($user)->postJson(
            sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid),
            ['mailVariant' => 'invalid-variant'],
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function testMissingTaskEmail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat2a(),
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = null;
                $general->firstname = $this->faker->firstName();
                $general->lastname = $this->faker->lastName();
                $general->phone = $this->faker->phoneNumber;
            }),
        ]);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(400);
        $this->assertEquals(['error' => 'send message to task failed'], $response->json());
    }

    public function testAddedText(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $addedText = $this->faker->paragraph;

        $this->mockMessageTransportAndAssertSend();

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
            'addedText' => $addedText,
        ]);
        $this->assertStringContainsString($addedText, $response->json('text'));
    }

    public function testMissingCustomPlaceholderIfNoCustomText(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $this->mockMessageTransportAndAssertSend();

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $this->assertStringNotContainsString('%custom_text_placeholder%', $response->json('text'));
    }

    public function testPostWithAttachments(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        /** @var AttachmentService $attachmentService */
        $attachmentService = $this->app->get(AttachmentService::class);
        $availableAttachments = $attachmentService->getAttachmentsForTemplateType(MessageTemplateType::contactInfection());

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
            'attachments' => array_map(static fn($a): string => $a['uuid'], $availableAttachments),
        ]);
        $response->assertStatus(200);
        $messageUuid = $response->json('uuid');

        $this->assertDatabaseHas('message', [
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'user_uuid' => $user->uuid,
        ]);

        $this->assertTrue(count($availableAttachments) > 0);
        foreach ($availableAttachments as $availableAttachment) {
            $this->assertDatabaseHas('message_attachment', [
                'message_uuid' => $messageUuid,
                'attachment_uuid' => $availableAttachment['uuid'],
            ]);
        }
    }

    public function testPostWhenMessageExceptionIsThrown(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send')
                ->andThrows(new MessageException('HTTP request returned status code 405'));
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $task->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(400);
    }

    protected function createTaskForCase(EloquentCase $case, array $taskAttributes = []): EloquentTask
    {
        if ($taskAttributes === []) {
            $taskAttributes = [
                'created_at' => CarbonImmutable::now(),
                'category' => ContactCategory::cat2a(),
                'general' => General::newInstanceWithVersion(1, function (General $general): void {
                    $general->email = $this->faker->safeEmail;
                    $general->firstname = $this->faker->firstName();
                    $general->lastname = $this->faker->lastName();
                    $general->phone = $this->faker->phoneNumber;
                }),
            ];
        }

        return parent::createTaskForCase($case, $taskAttributes);
    }

    private function mockMessageTransportAndAssertSend(): void
    {
        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });
    }
}
