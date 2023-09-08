<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\AlternativeLanguage\AlternativeLanguageCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class AlternativeLanguage extends FragmentCompat implements AlternativeLanguageCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\AlternativeLanguage');
        $schema->setDocumentationIdentifier('task.alternativeLanguage');

        $schema->add(YesNoUnknown::getVersion(1)->createField('useAlternativeLanguage'));
        $schema->add(Language::getVersion(1)->createArrayField('phoneLanguages'));
        $schema->add(EmailLanguage::getVersion(1)->createField('emailLanguage'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
