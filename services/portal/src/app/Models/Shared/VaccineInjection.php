<?php

declare(strict_types=1);

namespace App\Models\Shared;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Shared\VaccineInjection\VaccineInjectionCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationRule;
use MinVWS\DBCO\Enum\Models\Vaccine;

use function app;

class VaccineInjection extends Entity implements SchemaProvider, VaccineInjectionCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Shared\\VaccineInjection');
        $schema->setDocumentationIdentifier('shared.vaccineInjection');

        $schema->add(DateTimeType::createField('injectionDate', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ])
            ->addWarning('after_or_equal:2021-01-06', [
                ValidationRule::TAG_OSIRIS_INITIAL,
                ValidationRule::TAG_OSIRIS_FINAL,
            ]);

        $schema->add(BoolType::createField('isInjectionDateEstimated'));
        $schema->add(Vaccine::getVersion(1)->createField('vaccineType'));
        $schema->add(StringType::createField('otherVaccineType'))
            ->getValidationRules()
            ->addWarning('max:500');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
