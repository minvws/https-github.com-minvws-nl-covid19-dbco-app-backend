<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Message;

use App\Models\CovidCase\Contact;
use App\Models\Eloquent\EloquentCase;
use App\Services\Message\MessageFactoryService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

class MessageFactoryServiceTest extends FeatureTestCase
{
    private MessageFactoryService $messageFactoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageFactoryService = $this->app->get(MessageFactoryService::class);
    }

    public function testCreate(): void
    {
        $organisationName = $this->faker->company();
        $fromEmail = $this->faker->safeEmail;

        config()->set('mail.from.address', $fromEmail);

        $user = $this->createUser();
        $case = $this->createCaseForMessage(['name' => $organisationName]);

        $message = $this->messageFactoryService->create($user, MessageTemplateType::missedPhone(), $case);

        $this->assertEquals($case->uuid, $message->case_uuid);
        $this->assertEquals('Belpoging van uw GGD', $message->subject);
        $this->assertEquals($fromEmail, $message->from_email);
        $this->assertEquals($organisationName, $message->from_name);
    }

    public function testCreateWithExpiryDays(): void
    {
        CarbonImmutable::setTestNow($this->faker->dateTime);

        $expiryDays = $this->faker->randomNumber(2);
        config()->set('messagetemplate.missed_phone.expiry_days', $expiryDays);

        $user = $this->createUser();
        $case = $this->createCaseForMessage();

        $message = $this->messageFactoryService->create($user, MessageTemplateType::missedPhone(), $case);

        $this->assertEquals($expiryDays, CarbonImmutable::now()->diffInDays($message->expires_at));
    }

    #[DataProvider('expiryDaysIncorrectConfigDataProvider')]
    public function testCreateWithExpiryDaysIsNotInt(string|null $configValue): void
    {
        config()->set('messagetemplate.missed_phone.expiry_days', $configValue);

        $user = $this->createUser();
        $case = $this->createCaseForMessage();

        $message = $this->messageFactoryService->create($user, MessageTemplateType::missedPhone(), $case);

        $this->assertNull($message->expires_at);
    }

    public static function expiryDaysIncorrectConfigDataProvider(): array
    {
        return [
            'string' => ['somestring'],
            'null' => [null],
        ];
    }

    private function createCaseForMessage(array $organisationAttributes = []): EloquentCase
    {
        $organisation = $this->createOrganisation($organisationAttributes);

        return $this->createCaseForOrganisation($organisation, [
            'contact' => Contact::newInstanceWithVersion(1, function (Contact $contact): void {
                $contact->email = $this->faker->safeEmail;
                $contact->phone = $this->faker->phoneNumber;
            }),
        ]);
    }
}
