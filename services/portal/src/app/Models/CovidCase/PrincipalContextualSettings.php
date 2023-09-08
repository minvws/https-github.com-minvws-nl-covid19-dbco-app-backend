<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\PrincipalContextualSettings\PrincipalContextualSettingsCommon;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;

use function app;

class PrincipalContextualSettings extends AbstractCovidCaseFragment implements PrincipalContextualSettingsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\PrincipalContextualSettings');
        $schema->setDocumentationIdentifier('covidCase.principalContextualSettings');

        $schema->add(BoolType::createField('hasPrincipalContextualSettings'));
        $schema->add(StringType::createArrayField('items'));
        $schema->add(StringType::createArrayField('otherItems'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
