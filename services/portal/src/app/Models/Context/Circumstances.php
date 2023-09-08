<?php

declare(strict_types=1);

namespace App\Models\Context;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Context\Circumstances\CircumstancesCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\CovidMeasure;
use MinVWS\DBCO\Enum\Models\PersonalProtectiveEquipment;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Circumstances extends FragmentCompat implements CircumstancesCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Context\\Circumstances');
        $schema->setDocumentationIdentifier('context.circumstances');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isUsingPPE'));
        $schema->add(StringType::createField('ppeType'));
        $schema->add(PersonalProtectiveEquipment::getVersion(1)->createArrayField('usedPersonalProtectiveEquipment'));
        $schema->add(StringType::createField('ppeReplaceFrequency'))
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(BoolType::createField('ppeMedicallyCompetent'));
        $schema->add(CovidMeasure::getVersion(1)->createArrayField('covidMeasures'));
        $schema->add(StringType::createArrayField('otherCovidMeasures'))
            ->getElementValidationRules()
            ->addFatal('max:250');
        $schema->add(YesNoUnknown::getVersion(1)->createField('causeForConcern'));
        $schema->add(StringType::createField('causeForConcernRemark'))
            ->getValidationRules()
            ->addFatal('max:5000');
        $schema->add(BoolType::createField('sharedTransportation'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
