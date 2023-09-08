<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Communication\CommunicationCommon;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\IsolationAdvice;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Communication extends AbstractCovidCaseFragment implements CommunicationCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(4);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Communication');
        $schema->setDocumentationIdentifier('covidCase.communication');

        $schema->add(StringType::createField('otherAdviceGiven'))
            ->getValidationRules()
            ->addFatal('max:5000');

        $schema->add(StringType::createField('particularities'))
            ->getValidationRules()
            ->addFatal('max:5000');

        // Fields up to version 1
        $schema->add(IsolationAdvice::getVersion(1)->createArrayField('isolationAdviceGiven'))
            ->setMaxVersion(1);

        // Fields from version 2
        $schema->add(IsolationAdvice::getVersion(2)->createArrayField('isolationAdviceGiven'))
            ->setMinVersion(2)
            ->setMaxVersion(3);

        // Fields up to version 3
        $schema->add(StringType::createField('conditionalAdviceGiven'))
            ->setMaxVersion(3)
            ->getValidationRules()
            ->addFatal('max:5000');

        // Fields from version 3
        $schema->add(YesNoUnknown::getVersion(1)->createField('scientificResearchConsent'))
            ->setMinVersion(3)
            ->getValidationRules();

        $schema->add(StringType::createField('remarksRivm'))
            ->setMinVersion(3)
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
