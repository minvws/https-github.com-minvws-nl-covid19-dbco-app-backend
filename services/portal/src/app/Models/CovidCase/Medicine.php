<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Medication\MedicineCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;

use function app;

class Medicine extends FragmentCompat implements MedicineCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Medication');
        $schema->setDocumentationIdentifier('covidCase.medicine');

        $schema->add(StringType::createField('name')->setAllowsNull(false))
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(StringType::createField('remark'))
            ->getValidationRules()
            ->addWarning('max:5000');
        $schema->add(StringType::createField('knownEffects'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
