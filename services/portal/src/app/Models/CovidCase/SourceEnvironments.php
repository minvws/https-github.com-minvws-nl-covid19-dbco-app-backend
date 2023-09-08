<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\SourceEnvironments\SourceEnvironmentsCommon;
use App\Schema\Schema;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class SourceEnvironments extends AbstractCovidCaseFragment implements SourceEnvironmentsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\SourceEnvironments');
        $schema->setDocumentationIdentifier('covidCase.sourceEnvironments');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasLikelySourceEnvironments'))->setMaxVersion(1);
        $schema->add(ContextCategory::getVersion(1)->createArrayField('likelySourceEnvironments'))->setMaxVersion(1);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
