<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\Symptoms\SymptomsCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Symptoms extends FragmentCompat implements SymptomsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Symptoms');
        $schema->setDocumentationIdentifier('task.symptoms');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasSymptoms'));
        $schema->add(Symptom::getVersion(1)->createArrayField('symptoms'));
        $schema->add(StringType::createArrayField('otherSymptoms'))
            ->getElementValidationRules()
            ->addFatal('max:100');
        $schema->add(DateTimeType::createField('dateOfSymptomOnset', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('date_format:Y-m-d')
            ->addWarning('before_or_equal:today');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
