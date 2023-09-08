<?php

declare(strict_types=1);

namespace App\Services\Message;

use Illuminate\View\Factory as ViewFactory;
use MinVWS\DBCO\Enum\Models\EmailLanguage;

use function app;
use function is_null;
use function sprintf;

class EmailTemplateHelper
{
    private static ?ViewFactory $view = null;

    public static function getView(): ViewFactory
    {
        if (is_null(self::$view)) {
            self::$view = app(ViewFactory::class);
        }
        return self::$view;
    }

    public static function getTemplatePath(EmailLanguage $emailLanguage, string $filename): string
    {
        return sprintf('mail/templates/%s/%s', $emailLanguage->value, $filename);
    }

    public static function validateEmailLanguageOrFallbackToDefault(
        EmailLanguage $emailLanguage,
        string $filename,
    ): EmailLanguage {
        if (self::templateExists($emailLanguage, $filename)) {
            return $emailLanguage;
        }

        return self::defaultEmailLanguage();
    }

    public static function defaultEmailLanguage(): EmailLanguage
    {
        return EmailLanguage::nl();
    }

    public static function templateExists(EmailLanguage $emailLanguage, string $filename): bool
    {
        $templatePath = self::getTemplatePath($emailLanguage, $filename);

        return self::getView()->exists($templatePath);
    }
}
