<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\GeneralPractitioner\GeneralPractitionerCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;

use function app;

class GeneralPractitioner extends FragmentCompat implements GeneralPractitionerCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\GeneralPractitioner');
        $schema->setDocumentationIdentifier('covidCase.generalPractitioner');

        $schema->add(StringType::createField('name'))->getValidationRules()->addWarning('max:250');
        $schema->add(StringType::createField('practiceName'))->getValidationRules()->addWarning('max:250');
        $schema->add(GeneralPractitionerAddress::getSchema()->getCurrentVersion()->createField('address'));
        $schema->add(BoolType::createField('hasInfectionNotificationConsent'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
