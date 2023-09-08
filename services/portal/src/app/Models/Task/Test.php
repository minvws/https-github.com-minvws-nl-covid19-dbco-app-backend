<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\Test\TestCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Services\CaseNumberService;
use Closure;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Test extends FragmentCompat implements TestCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Test');
        $schema->setDocumentationIdentifier('task.test');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isTested'));
        $schema->add(TestResult::getVersion(1)->createField('testResult'));
        $schema->add(DateTimeType::createField('dateOfTest', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isReinfection'));
        $schema->add(DateTimeType::createField('previousInfectionDateOfSymptom', 'Y-m-d'))
            ->getValidationRules()
            ->addWarning('before_or_equal:today');
        $schema->add(YesNoUnknown::getVersion(1)->createField('previousInfectionReported'));
        $schema->add(StringType::createField('previousInfectionHpzoneNumber'))->setMaxVersion(1);

        // Fields from version 2
        $schema->add(StringType::createField('previousInfectionCaseNumber'))
            ->setMinVersion(2)
            ->getValidationRules()
            ->addWarning('regex:' . CaseNumberService::CASE_NUMBER_REGEX);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPreviousInfectionCaseNumberFieldValue(?string $value, Closure $setter): void
    {
        $setter(CaseNumberService::sanitizeCaseNumber($value));
    }
}
