<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\RecentBirth\RecentBirthCommon;
use App\Schema\Schema;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;
use function is_string;
use function sprintf;

class RecentBirth extends AbstractCovidCaseFragment implements RecentBirthCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\RecentBirth');
        $schema->setDocumentationIdentifier('covidCase.recentBirth');

        // Fields up to version 1
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasRecentlyGivenBirth'))->setMaxVersion(1);
        $schema->add(DateTimeType::createField('birthDate', 'Y-m-d'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addWarning('before_or_equal:today')
            ->addWarning(
                static fn (ValidationContext $context) => is_string($context->getValue('maxRecentBirthBeforeCaseCreation'))
                    ? sprintf('after_or_equal:%s', $context->getValue('maxRecentBirthBeforeCaseCreation'))
                    : '',
            );
        $schema->add(StringType::createField('birthRemarks'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addWarning('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
