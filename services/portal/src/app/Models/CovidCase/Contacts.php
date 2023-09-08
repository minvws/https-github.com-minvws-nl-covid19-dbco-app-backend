<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Contacts\ContactsCommon;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Contacts extends AbstractCovidCaseFragment implements ContactsCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(2);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Contacts');
        $schema->setDocumentationIdentifier('covidCase.contacts');

        $schema->add(StringType::createField('shareNameWithContacts'))
            ->getValidationRules()
            ->addFatal('in:yes,no,specified');
        $schema->add(YesNoUnknown::getVersion(1)->createField('estimatedMissingContacts'))
            ->setMinVersion(2);
        $schema->add(IntType::createField('estimatedCategory1Contacts'))
            ->setMinVersion(2)
            ->getValidationRules()
            ->addFatal('min:0');
        $schema->add(IntType::createField('estimatedCategory2Contacts'))
            ->setMinVersion(2)
            ->getValidationRules()
            ->addFatal('min:0');
        $schema->add(IntType::createField('estimatedCategory3Contacts'))
            ->getValidationRules()
            ->addFatal('min:0');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
