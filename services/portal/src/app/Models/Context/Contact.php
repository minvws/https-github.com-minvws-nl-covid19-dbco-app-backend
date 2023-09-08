<?php

declare(strict_types=1);

namespace App\Models\Context;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Context\Contact\ContactCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use Closure;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

/**
 * Contact (person).
 */
class Contact extends FragmentCompat implements ContactCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Context\\Contact');
        $schema->setDocumentationIdentifier('context.contact');

        $schema->add(StringType::createField('firstname'))
            ->getValidationRules()
            ->addWarning('max:250');
        $schema->add(StringType::createField('lastname'))
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(StringType::createField('phone'))
            ->getValidationRules()
            ->addWarning('phone:INTERNATIONAL,NL')
            ->addFatal('max:25');
        $schema->add(BoolType::createField('isInformed'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('notificationConsent'));
        $schema->add(BoolType::createField('notificationNamedConsent'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPhoneFieldValue(?string $phone, Closure $setter): void
    {
        if (isset($phone)) {
            $phone = PhoneFormatter::format($phone);
        }
        $setter($phone);
    }
}
