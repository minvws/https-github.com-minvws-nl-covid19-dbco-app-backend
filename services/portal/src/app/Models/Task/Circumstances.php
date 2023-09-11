<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\Circumstances\CircumstancesCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
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
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Circumstances');
        $schema->setDocumentationIdentifier('task.circumstances');

        $schema->add(YesNoUnknown::getVersion(1)->createField('wasUsingPPE'));
        $schema->add(
            PersonalProtectiveEquipment::getVersion(1)
                ->createArrayField('usedPersonalProtectiveEquipment'),
        );
        $schema->add(StringType::createField('ppeType'))
            ->getValidationRules()
            ->addFatal('max:250');
        $schema->add(StringType::createField('ppeReplaceFrequency'))
            ->getValidationRules()
            ->addFatal('max:500');
        $schema->add(BoolType::createField('ppeMedicallyCompetent'))
            ->getValidationRules()
            ->addFatal('max:500');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
