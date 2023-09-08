<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\AlternateContact\AlternateContactCommon;
use App\Schema\Conditions\Condition;
use App\Schema\Schema;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use App\Schema\Validation\ValidationContext;
use Closure;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class AlternateContact extends AbstractCovidCaseFragment implements AlternateContactCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\AlternateContact');
        $schema->setDocumentationIdentifier('covidCase.alternateContact');

        $hasAlternativeContact = $schema->add(YesNoUnknown::getVersion(1)->createField('hasAlternateContact'));
        $hasAlternativeContactIsYes = Condition::field($hasAlternativeContact)->identicalTo(YesNoUnknown::yes());

        $schema->add(Gender::getVersion(1)->createField('gender'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE);

        $schema->add(Relationship::getVersion(1)->createField('relationship'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE);

        $schema->add(StringType::createField('firstname'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['required', 'max:250'];
                }
                    return [];
            });

        $schema->add(StringType::createField('lastname'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['max:500'];
                }
                    return [];
            });

        $schema->add(StringType::createField('phone'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['required_without:email', 'max:25', "phone:INTERNATIONAL,NL"];
                }
                    return [];
            });

        $schema->add(StringType::createField('email'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE)
            ->getValidationRules()
            ->addWarning(static function (ValidationContext $context) {
                if ($context->getValue('hasAlternateContact') === YesNoUnknown::yes()->value) {
                    return ['email', 'max:250'];
                }
                    return [];
            });

        $schema->add(BoolType::createField('isDefaultContact'))
            ->setEncodingCondition($hasAlternativeContactIsYes, EncodingContext::MODE_STORE);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPhoneFieldValue(?string $phone, Closure $setter): void
    {
        $setter($phone !== null ? PhoneFormatter::format($phone) : null);
    }
}
