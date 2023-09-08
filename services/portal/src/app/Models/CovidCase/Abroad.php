<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Abroad\AbroadCommon;
use App\Schema\Schema;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Abroad extends AbstractCovidCaseFragment implements AbroadCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Abroad');
        $schema->setDocumentationIdentifier('covidCase.abroad');

        $schema->add(YesNoUnknown::getVersion(1)->createField('wasAbroad'));
        $schema->add(Trip::getSchema()->getVersion(1)->createArrayField('trips'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
