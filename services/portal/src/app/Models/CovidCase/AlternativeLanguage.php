<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\AlternativeLanguage\AlternativeLanguageCommon;
use App\Schema\Conditions\Condition;
use App\Schema\Schema;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class AlternativeLanguage extends AbstractCovidCaseFragment implements AlternativeLanguageCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\AlternativeLanguage');
        $schema->setDocumentationIdentifier('covidCase.alternativeLanguage');

        $useAlternativeLanguage = $schema->add(YesNoUnknown::getVersion(1)->createField('useAlternativeLanguage'));
        $useAlternativeLanguageIsYes = Condition::field($useAlternativeLanguage)->identicalTo(YesNoUnknown::yes());

        $schema->add(EmailLanguage::getVersion(1)->createField('emailLanguage'))
            ->setEncodingCondition($useAlternativeLanguageIsYes, EncodingContext::MODE_STORE);

        $schema->add(Language::getVersion(1)->createArrayField('phoneLanguages'))
            ->setEncodingCondition($useAlternativeLanguageIsYes, EncodingContext::MODE_STORE);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
