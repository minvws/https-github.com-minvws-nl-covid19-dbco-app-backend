<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Task\AlternateContact\AlternateContactCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class AlternateContact extends FragmentCompat implements AlternateContactCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task\\AlternateContact');
        $schema->setDocumentationIdentifier('task.alternateContact');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasAlternateContact'));
        $schema->add(Gender::getVersion(1)->createField('gender'));
        $schema->add(Relationship::getVersion(1)->createField('relationship'));
        $schema->add(StringType::createField('firstname'))
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['required', 'max:250'];
                }
                    return [];
            });

        $schema->add(StringType::createField('lastname'))
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['max:500'];
                }
                    return [];
            });

        $schema->add(StringType::createField('explanation'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
