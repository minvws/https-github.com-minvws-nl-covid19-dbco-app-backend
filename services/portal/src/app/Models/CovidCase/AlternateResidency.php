<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Shared\Address;
use App\Models\Versions\CovidCase\AlternateResidency\AlternateResidencyCommon;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class AlternateResidency extends AbstractCovidCaseFragment implements AlternateResidencyCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\AlternateResidency');
        $schema->setDocumentationIdentifier('covidCase.alternateResidency');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasAlternateResidency'));
        $schema->add(Address::getSchema()->getVersion(1)->createField('address'));
        $schema->add(StringType::createField('remark'))->getValidationRules()->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
