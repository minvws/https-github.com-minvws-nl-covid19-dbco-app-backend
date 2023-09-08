<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\AlternativeLanguage\AlternativeLanguageCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class AlternativeLanguageEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof AlternativeLanguageCommon);

        $container->useAlternativeLanguage = $object->useAlternativeLanguage;

        if ($object->useAlternativeLanguage !== YesNoUnknown::yes()) {
            return;
        }

        $container->emailLanguage = $object->emailLanguage;
        $container->phoneLanguages = $object->phoneLanguages;
    }
}
