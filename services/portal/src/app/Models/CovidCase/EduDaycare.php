<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\EduDaycare\EduDaycareCommon;
use App\Schema\Schema;
use MinVWS\DBCO\Enum\Models\EduDaycareType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class EduDaycare extends AbstractCovidCaseFragment implements EduDaycareCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\EduDaycare');
        $schema->setDocumentationIdentifier('covidCase.eduDayCare');

        $schema->add(YesNoUnknown::getVersion(1)->createField('isStudent'))->setMaxVersion(1);
        $schema->add(EduDaycareType::getVersion(1)->createField('type'))->setMaxVersion(1);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
