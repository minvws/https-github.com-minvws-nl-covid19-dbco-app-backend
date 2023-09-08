<?php

declare(strict_types=1);

namespace App\Models\Shared;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\IntType;
use App\Schema\Validation\ValidationRule;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * Defines a base schema structure for vaccination fragments.
 */
abstract class BaseVaccination extends FragmentCompat
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(static::class);

        $schema->add(YesNoUnknown::getVersion(1)->createField('isVaccinated'));

        // Fields up to version 1
        $schema->add(BoolType::createField('hasCompletedVaccinationSeries'))
            ->setMaxVersion(1);

        // Fields up to version 2
        $schema->add(VaccineInjection::getSchema()->getVersion(1)->createArrayField('vaccineInjections'))
            ->setMaxVersion(2);

        // Fields starting from version 3
        $schema->add(VaccineInjection::getSchema()->getVersion(1)->createArrayField('vaccineInjections'))
            ->setMinVersion(3)
            ->getValidationRules()
            ->addWarning('array')
            ->addWarning('min:0')
            ->addWarning('max:1');

        $schema->add(IntType::createField('vaccinationCount'))
            ->setMinVersion(3)
            ->getValidationRules()
            ->addWarning('numeric', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ])
            ->addWarning('min:0', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ])
            ->addNotice('numeric', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ])
            ->addNotice('max:6', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ]);


        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
