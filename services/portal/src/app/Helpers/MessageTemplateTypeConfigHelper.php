<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\MessageTemplateType;

use function sprintf;

class MessageTemplateTypeConfigHelper
{
    public static function getConfig(MessageTemplateType $messageTemplateType): array
    {
        return Config::array(sprintf('messagetemplate.%s', self::getConfigName($messageTemplateType)));
    }

    public static function isEnabled(MessageTemplateType $messageTemplateType): bool
    {
        return FeatureFlagHelper::isEnabled(sprintf('message_template.%s', self::getConfigName($messageTemplateType)));
    }

    public static function isDisabled(MessageTemplateType $messageTemplateType): bool
    {
        return !self::isEnabled($messageTemplateType);
    }

    public static function isSecure(MessageTemplateType $messageTemplateType): bool
    {
        return self::getConfig($messageTemplateType)['secure'];
    }

    private static function getConfigName(MessageTemplateType $messageTemplateType): string
    {
        return Str::snake($messageTemplateType->value);
    }
}
