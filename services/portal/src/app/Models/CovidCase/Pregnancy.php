<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Pregnancy\PregnancyCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;
use function is_string;
use function sprintf;

class Pregnancy extends FragmentCompat implements PregnancyCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Pregnancy');
        $schema->setDocumentationIdentifier('covidCase.pregnancy');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isPregnant'));
        $schema->add(DateTimeType::createField('dueDate', 'Y-m-d'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxDueDateAfterCaseCreation'))
                    ? sprintf('before_or_equal:%s', $context->getValue('maxDueDateAfterCaseCreation'))
                    : '',
            )
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxDueDateBeforeCaseCreation'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxDueDateBeforeCaseCreation'))
                    : '',
            );
        $schema->add(StringType::createField('remarks'))
            ->setMinVersion(2)
            ->getValidationRules()
            ->addWarning('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
