<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingCommon;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\BCOType;
use MinVWS\DBCO\Enum\Models\ExtensiveContactTracingReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class ExtensiveContactTracing extends AbstractCovidCaseFragment implements ExtensiveContactTracingCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(3);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\ExtensiveContactTracing');
        $schema->setDocumentationIdentifier('covidCase.extensiveContactTracing');

        // Fields up to version 1
        $schema->add(YesNoUnknown::getVersion(1)->createField('receivesExtensiveContactTracing'))->setMaxVersion(1);

        // Fields up to version 2
        $schema->add(ExtensiveContactTracingReason::getVersion(1)->createArrayField('reasons'))->setMaxVersion(2);
        $schema->add(StringType::createField('notes'))
            ->setMaxVersion(2)
            ->getValidationRules()
            ->addWarning('max:5000');

        // Fields starting from version 2
        $schema->add(BCOType::getVersion(1)->createField('receivesExtensiveContactTracing'))->setMinVersion(2);

        $schema->add(StringType::createField('otherDescription'))->setMinVersion(2)
            ->getValidationRules()
            ->addWarning('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
