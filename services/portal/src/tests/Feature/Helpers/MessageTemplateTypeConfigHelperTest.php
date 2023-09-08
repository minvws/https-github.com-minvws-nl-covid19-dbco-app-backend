<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\MessageTemplateTypeConfigHelper;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

class MessageTemplateTypeConfigHelperTest extends FeatureTestCase
{
    public function testGetConfig(): void
    {
        $missedPhoneConfig = ['secure' => $this->faker->boolean];
        config()->set('messagetemplate.missed_phone', $missedPhoneConfig);

        $config = MessageTemplateTypeConfigHelper::getConfig(MessageTemplateType::missedPhone());

        $this->assertEquals($config, $missedPhoneConfig);
    }

    public function testGetConfigArrayAssertion(): void
    {
        config()->set('messagetemplate.missed_phone', 'notAnArray');

        $this->expectException(InvalidArgumentException::class);
        MessageTemplateTypeConfigHelper::getConfig(MessageTemplateType::missedPhone());
    }

    #[DataProvider('isSecureDataProvider')]
    public function testIsSecure(bool $isSecure): void
    {
        $missedPhoneConfig = ['secure' => $isSecure];
        config()->set('messagetemplate.missed_phone', $missedPhoneConfig);

        $this->assertEquals($isSecure, MessageTemplateTypeConfigHelper::isSecure(MessageTemplateType::missedPhone()));
    }

    public static function isSecureDataProvider(): array
    {
        return [
            'secure' => [true],
            'not secure' => [false],
        ];
    }

    public function testIsEnabled(): void
    {
        config()->set('featureflag.message_template.missed_phone', true);

        $this->assertTrue(MessageTemplateTypeConfigHelper::isEnabled(MessageTemplateType::missedPhone()));
    }

    public function testIsDisabled(): void
    {
        config()->set('featureflag.message_template.missed_phone', false);

        $this->assertTrue(MessageTemplateTypeConfigHelper::isDisabled(MessageTemplateType::missedPhone()));
    }
}
