<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\RiskLocation\RiskLocationCommon;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class RiskLocation extends AbstractCovidCaseFragment implements RiskLocationCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\RiskLocation');
        $schema->setDocumentationIdentifier('covidCase.riskLocation');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isLivingAtRiskLocation'))
            ->getValidationRules()
            ->addOsirisFinished('required');

        $schema->add(RiskLocationType::getVersion(1)->createField('type'))
            ->getValidationRules()
            ->addOsirisFinished('required_if:isLivingAtRiskLocation,' . YesNoUnknown::yes());

        $schema->add(StringType::createField('otherType'))
            ->getValidationRules()
            ->addFatal('max:5000');

        // Fields from version 2
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasRelatedSickness'))
            ->setMinVersion(2)
            ->getValidationRules();

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasDifferentDiseaseCourse'))
            ->setMinVersion(2)
            ->getValidationRules();

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
