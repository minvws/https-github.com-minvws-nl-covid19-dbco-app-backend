<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\Message;

use App\Models\Task\AlternativeLanguage;
use App\Models\Task\General;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function sprintf;

#[Group('message')]
#[Group('case-message')]
#[Group('case')]
class ApiCaseMessageToTaskTemplateControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('message_template.missed_phone');
    }

    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                    $general->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(
                sprintf(
                    '/api/cases/%s/messages/template/%s/%s',
                    $case->uuid,
                    MessageTemplateType::missedPhone(),
                    $task->uuid,
                ),
                ['type' => MessageTemplateType::personalAdvice()->value],
            );
        $response->assertStatus(200);
        $this->assertDatabaseMissing('message', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testGetForNonExistingLanguageTask(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'alternativeLanguage' => AlternativeLanguage::newInstanceWithVersion(
                1,
                static function (AlternativeLanguage $alternativeLanguage): void {
                    $alternativeLanguage->useAlternativeLanguage = YesNoUnknown::yes();
                    $alternativeLanguage->emailLanguage = EmailLanguage::ar();
                },
            ),
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(
                sprintf(
                    '/api/cases/%s/messages/template/%s/%s',
                    $case->uuid,
                    MessageTemplateType::missedPhone(),
                    $task->uuid,
                ),
                ['type' => MessageTemplateType::personalAdvice()->value],
            );
        $response->assertStatus(200);
        $this->assertEquals('nl', $response->json('language'));
    }

    #[DataProvider('customTextPlaceholderDataProvider')]
    public function testCustomTextPlaceholder(MessageTemplateType $messageTemplateType, bool $expectPresent): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s/%s', $case->uuid, $messageTemplateType, $task->uuid));

        if ($expectPresent) {
            $this->assertStringContainsString('%custom_text_placeholder%', $response->json('body'));
        } else {
            $this->assertStringNotContainsString('%custom_text_placeholder%', $response->json('body'));
        }
    }

    public static function customTextPlaceholderDataProvider(): array
    {
        return [
            'missing on missed_phone' => [MessageTemplateType::missedPhone(), false],
            'present on personal_advice' => [MessageTemplateType::personalAdvice(), true],
        ];
    }

    public function testGetWhenTemplateDisabled(): void
    {
        ConfigHelper::disableFeatureFlag('message_template.missed_phone');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $task = $this->createTaskForCase($case, [
            'general' => General::newInstanceWithVersion(1, function (General $general): void {
                $general->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(
                sprintf(
                    '/api/cases/%s/messages/template/%s/%s',
                    $case->uuid,
                    MessageTemplateType::missedPhone(),
                    $task->uuid,
                ),
                ['type' => MessageTemplateType::personalAdvice()->value],
            );
        $response->assertStatus(400);
    }
}
