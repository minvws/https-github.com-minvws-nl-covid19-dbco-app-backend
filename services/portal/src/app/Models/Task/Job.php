<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\Job\JobCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Job extends FragmentCompat implements JobCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Job');
        $schema->setDocumentationIdentifier('task.job');

        $schema->add(YesNoUnknown::getVersion(1)->createField('worksInAviation'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('worksInHealthCare'));
        $schema->add(StringType::createField('healthCareFunction'))
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
