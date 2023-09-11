<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\Message;

use App\Exceptions\MessageException;
use App\Models\CovidCase\Contact;
use App\Services\Message\AttachmentService;
use App\Services\Message\MessageFactoryService;
use App\Services\Message\MessageTransportService;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use Mockery\MockInterface;
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
class ApiCaseMessageToIndexControllerTest extends FeatureTestCase
{
    use CaseFragmentGenerator;

    public function testPost(): void
    {
        ConfigHelper::enableFeatureFlag('message_template.advice');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('message', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testAddedText(): void
    {
        ConfigHelper::enableFeatureFlag('message_template.advice');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);

        $addedText = $this->faker->paragraph;

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
            'addedText' => $addedText,
        ]);
        $this->assertStringContainsString($addedText, $response->json('text'));
    }

    public function testMissingCustomPlaceholderIfNoCustomText(): void
    {
        ConfigHelper::enableFeatureFlag('message_template.advice');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $this->assertStringNotContainsString('%custom_text_placeholder%', $response->json('text'));
    }

    public function testTemplateDisabled(): void
    {
        ConfigHelper::disableFeatureFlag('message_template.advice');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(400);
    }

    public function testNonValidCaseUuid(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->postJson('/api/cases/nonexisting/messages');
        $response->assertStatus(404);
    }

    public function testMessageTemplateTypeIsRequired(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function testMessageTemplateTypeInvalid(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->postJson(
            sprintf('/api/cases/%s/messages', $case->uuid),
            ['mailVariant' => 'invalid-variant'],
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function testMissingIndexEmail(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = null;
                $contact->phone = $this->faker->phoneNumber;
            }),
        ]);

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(400);
        $this->assertEquals(['error' => 'send message to index failed'], $response->json());
    }

    public function testPostWithAttachments(): void
    {
        ConfigHelper::enableFeatureFlag('message_template.advice');

        [$availableAttachments, $messageUuid] = $this->postWithAttachments();

        $this->assertTrue(count($availableAttachments) > 0);
        foreach ($availableAttachments as $availableAttachment) {
            $this->assertDatabaseHas('message_attachment', [
                'message_uuid' => $messageUuid,
                'attachment_uuid' => $availableAttachment['uuid'],
            ]);
        }
    }

    public function testSendWithMessageException(): void
    {
        ConfigHelper::enableFeatureFlag('message_template.advice');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $errorMessage = $this->faker->sentence;

        $this->mock(MessageFactoryService::class, static function (MockInterface $mock) use ($errorMessage): void {
            $mock->expects('create')->andThrow(new MessageException($errorMessage));
        });

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
        ]);
        $response->assertStatus(400);
        $response->assertJson(['error' => 'send message to index failed']);
    }

    private function postWithAttachments(): array
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->updateCaseWithAllFragments($case);

        $this->mock(MessageTransportService::class, static function (MockInterface $mock): void {
            $mock->expects('send');
        });

        /** @var AttachmentService $attachmentService */
        $attachmentService = $this->app->get(AttachmentService::class);
        $availableAttachments = $attachmentService->getAttachmentsForTemplateType(MessageTemplateType::personalAdvice());

        $response = $this->be($user)->postJson(sprintf('/api/cases/%s/messages', $case->uuid), [
            'type' => MessageTemplateType::personalAdvice()->value,
            'attachments' => array_map(static fn($a): string => $a['uuid'], $availableAttachments),
        ]);
        $response->assertStatus(200);
        $messageUuid = $response->json('uuid');
        $this->assertDatabaseHas('message', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);

        return [$availableAttachments, $messageUuid];
    }
}
