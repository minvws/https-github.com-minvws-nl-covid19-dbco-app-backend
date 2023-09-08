<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Contact;
use App\Models\Task\General;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Traits\CaseFragmentGenerator;

use function config;
use function sprintf;

#[Group('message')]
#[Group('case-message')]
#[Group('case')]
final class ApiCaseMessageControllerTest extends FeatureTestCase
{
    use CaseFragmentGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.secure', 'log');
        config()->set('mail.insecure', 'log');
    }

    public function testGetMessages(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message1 = $this->createMessage([
            'case_uuid' => $case->uuid,
            'created_at' => CarbonImmutable::now(),
        ]);
        $this->createMessage([
            'case_uuid' => $case->uuid,
            'created_at' => CarbonImmutable::now()->subDays(1),
        ]);
        $this->createMessage([
            'case_uuid' => $case->uuid,
            'created_at' => CarbonImmutable::now()->subDays(2),
        ]);
        // deleted message should not be in the result
        $this->createMessage([
            'case_uuid' => $case->uuid,
            'deleted_at' => CarbonImmutable::now()->subDays(2),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages', $case->uuid));
        $response->assertStatus(200);

        $this->assertCount(3, $response->json('messages'));

        // assert encoding of messageSummary
        $expectedResponseMessage1 = [
            'uuid' => $message1->uuid,
            'mailVariant' => $message1->message_template_type->value,
            'caseUuid' => $message1->case_uuid,
            'taskUuid' => $message1->task_uuid,
            'toEmail' => $message1->to_email,
            'toName' => $message1->to_name,
            'telephone' => $message1->telephone,
            'subject' => $message1->subject,
            'createdAt' => $message1->created_at->format('Y-m-d\TH:i:sp'),
            'notificationSentAt' => $message1->notification_sent_at?->format('Y-m-d\TH:i:sp'),
            'status' => $message1->status,
            'isExpired' => $message1->expires_at?->isPast() === true,
            'isDeleted' => $message1->deleted_at?->isPast() === true,
            'identityRequired' => $message1->identity_required,
            'isIdentified' => $message1->pseudo_bsn !== null,
            'hasAttachments' => false,
            'expiresAt' => $message1->expires_at?->format('Y-m-d\TH:i:sp'),
        ];
        $this->assertEquals($expectedResponseMessage1, $response->json('messages.2'));
    }

    #[Group('message-attachment')]
    public function testGetMessagesWithAttachments(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message1 = $this->createMessage([
            'case_uuid' => $case->uuid,
            'created_at' => $this->faker->dateTime(), //$message1 should be the first result when retrieving messages
        ]);

        $this->createMessage([
            'case_uuid' => $case->uuid,
            'created_at' => $message1->created_at->clone()->addMinute(),
        ]);

        $attachment1 = $this->createAttachment();
        $message1->attachments()->attach($attachment1);
        $attachment2 = $this->createAttachment();
        $message1->attachments()->attach($attachment2);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages', $case->uuid));
        $response->assertStatus(200);

        $this->assertTrue($response->json('messages.0.hasAttachments'));
        $this->assertFalse($response->json('messages.1.hasAttachments'));
    }

    #[Group('message-attachment')]
    public function testGetMessage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message = $this->createMessage([
            'case_uuid' => $case->uuid,
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $message->uuid));
        $response->assertStatus(200);

        $this->assertEquals($message->text, $response->json('text'));
    }

    #[DataProvider('getMessageWithAttachmentProvider')]
    #[Group('message-attachment')]
    public function testGetMessageWithAttachment(bool $hasAttachmentInactiveSince): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message = $this->createMessage([
            'case_uuid' => $case->uuid,
        ]);

        $attachmentUuid = $this->faker->uuid();
        $attachmentFileName = sprintf('%s.pdf', $this->faker->word());
        $attachmentCreatedAt = $this->faker->dateTime();
        $attachmentUpdatedAt = $this->faker->dateTimeBetween($attachmentCreatedAt);
        $attachmentInactiveSince = $hasAttachmentInactiveSince ? $this->faker->dateTime() : null;

        $attachment = $this->createAttachment([
            'uuid' => $attachmentUuid,
            'fileName' => $attachmentFileName,
            'createdAt' => $attachmentCreatedAt,
            'updatedAt' => $attachmentUpdatedAt,
            'inactiveSince' => $attachmentInactiveSince,
        ]);
        $message->attachments()->attach($attachment);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $message->uuid));
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertTrue($json['hasAttachments']);
        $this->assertCount(1, $json['attachments']);
        $this->assertEquals($attachmentUuid, $json['attachments'][0]['uuid']);
        $this->assertEquals($attachmentFileName, $json['attachments'][0]['fileName']);
        $this->assertEquals($attachmentCreatedAt->format('Y-m-d\TH:i:sp'), $json['attachments'][0]['createdAt']);
        $this->assertEquals($attachmentUpdatedAt->format('Y-m-d\TH:i:sp'), $json['attachments'][0]['updatedAt']);
        $this->assertEquals($attachmentInactiveSince?->format('Y-m-d\TH:i:sp'), $json['attachments'][0]['inactiveSince']);
    }

    public static function getMessageWithAttachmentProvider(): array
    {
        return [
            'Attachment with inactiveSince date' => [true],
            'Attachment without inactiveSince date' => [false],
        ];
    }

    #[Group('message-attachment')]
    public function testGetMessageWithIdentifiedIndex(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message = $this->createMessage([
            'case_uuid' => $case->uuid,
            'pseudo_bsn' => $this->faker->uuid(),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $message->uuid));
        $response->assertStatus(200);

        $this->assertTrue($response->json('isIdentified'));
    }

    #[Group('message-attachment')]
    public function testGetMessageWithoutAttachment(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $message = $this->createMessage([
            'case_uuid' => $case->uuid,
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages/%s', $case->uuid, $message->uuid));
        $response->assertStatus(200);

        $this->assertCount(0, $response->json('attachments'));
        $this->assertFalse($response->json('hasAttachments'));
    }

    public function testGetAllIndexMessages(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->safeEmail;
                $contact->phone = $this->faker->phoneNumber;
            }),
        ]);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat2a(),
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = $this->faker->safeEmail;
            }),
        ]);

        $this->createMessage([
            'case_uuid' => $case->uuid,
            'expires_at' => CarbonImmutable::now()->addDay(),
            'mail_template' => 'advice',
            'message_template_type' => MessageTemplateType::personalAdvice(),
        ]);
        $this->createMessage([
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'expires_at' => CarbonImmutable::now()->addDay(),
            'mail_template' => 'advice',
            'message_template_type' => MessageTemplateType::personalAdvice(),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/messages?only_for_index=true', $case->uuid));
        $response->assertStatus(200);

        $mailSummaries = $response->json();
        $this->assertCount(1, $mailSummaries['messages']);
        $this->assertEquals('personalAdvice', $mailSummaries['messages'][0]['mailVariant']);
        $this->assertEquals(null, $mailSummaries['messages'][0]['taskUuid']);
    }

    public function testGetAllTaskMessages(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->safeEmail;
                $contact->phone = $this->faker->phoneNumber;
            }),
        ]);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'category' => ContactCategory::cat2a(),
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = $this->faker->safeEmail;
            }),
        ]);

        $this->createMessage([
            'case_uuid' => $case->uuid,
            'expires_at' => CarbonImmutable::now()->addDay(),
            'message_template_type' => MessageTemplateType::contactInfection(),
        ]);
        $this->createMessage([
            'case_uuid' => $case->uuid,
            'task_uuid' => $task->uuid,
            'expires_at' => CarbonImmutable::now()->addDay(),
            'message_template_type' => MessageTemplateType::contactInfection(),
        ]);

        $response = $this->be($user)->getJson(
            sprintf('/api/cases/%s/messages?contact_uuid=%s', $case->uuid, $task->uuid),
        );
        $response->assertStatus(200);

        $mailSummaries = $response->json();
        $this->assertCount(1, $mailSummaries['messages']);
        $this->assertEquals('contactInfection', $mailSummaries['messages'][0]['mailVariant']);
        $this->assertEquals($task->uuid, $mailSummaries['messages'][0]['taskUuid']);
    }
}
