<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\Inform\InformCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\InformTarget;
use MinVWS\DBCO\Enum\Models\TaskAdvice;

use function app;

class Inform extends FragmentCompat implements InformCommon
{
    public const SHARE_INDEX_NAME_WITH_CONTACT_TRUE = 'yes';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(3);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\Inform');
        $schema->setDocumentationIdentifier('task.inform');

        $schema->add(InformStatus::getVersion(1)->createField('status'))->setProxyForOwnerField('informStatus');
        $schema->add(InformedBy::getVersion(1)->createField('informedBy'))->setProxyForOwnerField('communication');
        $schema->add(BoolType::createField('shareIndexNameWithContact'));
        $schema->add(InformTarget::getVersion(1)->createField('informTarget'));

        $schema->add(StringType::createField('otherAdvice'))
            ->getValidationRules()
            ->addFatal('max:5000');

        // Fields up to version 1
        $schema->add(TaskAdvice::getVersion(1)->createArrayField('advices'))
            ->setMaxVersion(1);

        $schema->add(StringType::createField('testAdvice'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addFatal('max:5000');

        $schema->add(StringType::createField('quarantineAdvice'))
            ->setMaxVersion(1)
            ->getValidationRules()
            ->addFatal('max:5000');

        // Fields from version 2
        $schema->add(TaskAdvice::getVersion(2)->createArrayField('advices'))
            ->setMinVersion(2)
            ->setMaxVersion(2);

        $schema->add(StringType::createField('vulnerableGroupsAdvice'))
            ->setMinVersion(2)
            ->setMaxVersion(2)
            ->getValidationRules()
            ->addFatal('max:5000');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
