<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\AlternativeLanguage\AlternativeLanguageCommon;
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

        $container->phoneLanguages = $object->phoneLanguages;
        $container->emailLanguage = $object->emailLanguage;
    }
}
