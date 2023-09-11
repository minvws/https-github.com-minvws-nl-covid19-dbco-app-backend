<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\Message;

use App\Models\CovidCase\AlternativeLanguage;
use App\Models\CovidCase\Contact;
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
class ApiCaseMessageToIndexTemplateControllerTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ConfigHelper::enableFeatureFlag('message_template.missed_phone');
    }

    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s', $case->uuid, MessageTemplateType::missedPhone()));
        $response->assertStatus(200);
        $this->assertDatabaseMissing('message', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testGetForEnglishLanguage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'alternativeLanguage' => AlternativeLanguage::newInstanceWithVersion(
                1,
                static function (AlternativeLanguage $alternativeLanguage): void {
                    $alternativeLanguage->useAlternativeLanguage = YesNoUnknown::yes();
                    $alternativeLanguage->emailLanguage = EmailLanguage::en();
                },
            ),
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s', $case->uuid, MessageTemplateType::personalAdvice()));
        $response->assertStatus(200);
        $this->assertEquals('en', $response->json('language'));
        $this->assertEquals('Advice from your GGD', $response->json('subject'));
    }

    public function testGetForNonExistingLanguage(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'alternativeLanguage' => AlternativeLanguage::newInstanceWithVersion(
                1,
                static function (AlternativeLanguage $alternativeLanguage): void {
                    $alternativeLanguage->useAlternativeLanguage = YesNoUnknown::yes();
                    $alternativeLanguage->emailLanguage = EmailLanguage::ar();
                },
            ),
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s', $case->uuid, MessageTemplateType::missedPhone()));
        $response->assertStatus(200);
        $this->assertEquals('nl', $response->json('language'));
    }

    #[DataProvider('customTextPlaceholderDataProvider')]
    public function testCustomTextPlaceholder(MessageTemplateType $messageTemplateType, bool $expectPresent): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->email;
            }),
        ]);

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s', $case->uuid, $messageTemplateType));

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

        $response = $this->be($user)
            ->get(sprintf('/api/cases/%s/messages/template/%s', $case->uuid, MessageTemplateType::missedPhone()));
        $response->assertStatus(400);
    }
}
