<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\PersonalDetails\PersonalDetailsCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\NoBsnOrAddressReason;

use function app;

class PersonalDetails extends FragmentCompat implements PersonalDetailsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\PersonalDetails');
        $schema->setDocumentationIdentifier('task.personalDetails');

        $schema->add(DateTimeType::createField('dateOfBirth', 'Y-m-d'))
            ->getValidationRules()
            ->addFatal('before_or_equal:today')
            ->addFatal('after_or_equal:1900-01-01');
        $schema->add(Gender::getVersion(1)->createField('gender'));
        $schema->add(TaskAddress::getSchema()->getVersion(1)->createField('address'))
            ->setDefaultValue(static fn() => TaskAddress::newInstanceWithVersion(1));
        $schema->add(StringType::createField('bsnCensored'))
            ->getValidationRules()
            ->addFatal('min:8')
            ->addFatal('max:9');
        $schema->add(StringType::createField('bsnLetters'))
            ->getValidationRules()
            ->addFatal('max:25');
        $schema->add(StringType::createField('bsnNotes'))
            ->getValidationRules()
            ->addFatal('max:5000');
        $schema->add(BoolType::createField('hasNoBsnOrAddress'))->setMaxVersion(1);
        $schema->add(NoBsnOrAddressReason::getVersion(1)->createArrayField('hasNoBsnOrAddress'))->setMinVersion(2);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
