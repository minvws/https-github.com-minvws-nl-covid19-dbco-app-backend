<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Immunity\ImmunityCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Immunity extends FragmentCompat implements ImmunityCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Immunity');
        $schema->setDocumentationIdentifier('covidCase.immunity');

        // Fields up to version 1
        $schema->add(YesNoUnknown::getVersion(1)->createField('isImmune'))->setMaxVersion(1);
        $schema->add(StringType::createField('remarks'))->setMaxVersion(1)->getValidationRules()->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
