<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Housemates\HousematesCommon;
use App\Schema\Conditions\Condition;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Housemates extends AbstractCovidCaseFragment implements HousematesCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Housemates');
        $schema->setDocumentationIdentifier('covidCase.housemates');

        $hasHouseMates = $schema->add(YesNoUnknown::getVersion(1)->createField('hasHouseMates'));
        $hasHouseMatesIsYes = Condition::field($hasHouseMates)->identicalTo(YesNoUnknown::yes());

        $schema->add(BoolType::createField('hasOwnFacilities'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE);

        $schema->add(BoolType::createField('hasOwnKitchen'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE);

        $schema->add(BoolType::createField('hasOwnBedroom'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE);

        $schema->add(BoolType::createField('hasOwnRestroom'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE);

        $schema->add(BoolType::createField('canStrictlyIsolate'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE);

        $schema->add(StringType::createField('bottlenecks'))
            ->setEncodingCondition($hasHouseMatesIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()->addWarning('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
