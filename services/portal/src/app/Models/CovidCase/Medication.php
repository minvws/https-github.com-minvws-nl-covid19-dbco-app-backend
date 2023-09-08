<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\CovidCase\Medication\MedicationCommon;
use App\Schema\FragmentCompat;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use Closure;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function app;

class Medication extends FragmentCompat implements MedicationCommon
{
    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\Medication');
        $schema->setDocumentationIdentifier('covidCase.medication');

        $schema->add(YesNoUnknown::getVersion(1)->createField('hasMedication'));
        $schema->add(YesNoUnknown::getVersion(1)->createField('isImmunoCompromised'));
        $schema->add(StringType::createField('immunoCompromisedRemarks'))
            ->getValidationRules()
            ->addWarning('max:5000');
        $schema->add(YesNoUnknown::getVersion(1)->createField('hasGivenPermission'));
        $schema->add(StringType::createField('practitioner'))
            ->getValidationRules()
            ->addWarning('max:500');
        $schema->add(StringType::createField('practitionerPhone'))
            ->getValidationRules()
            ->addWarning('phone:INTERNATIONAL,NL')
            ->addFatal('max:25');
        $schema->add(StringType::createField('hospitalName'))
            ->getValidationRules()
            ->addWarning('max:300');
        $schema->add(Medicine::getSchema()->getVersion(1)->createArrayField('medicines'));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected function setPractitionerPhoneFieldValue(?string $phone, Closure $setter): void
    {
        $setter($phone !== null ? PhoneFormatter::format($phone) : null);
    }
}
