<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Shared\Address;
use App\Models\Versions\CovidCase\GeneralPractitionerAddress\GeneralPractitionerAddressCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Fields\Field;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\SchemaType;

use function app;

class GeneralPractitionerAddress extends Entity implements SchemaProvider, GeneralPractitionerAddressCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\GeneralPractitionerAddress');
        $schema->setDocumentationIdentifier('covidCase.generalPractitionerAddress');

        Address::addDefaultValidationFields($schema);

        /** @var Field<SchemaType> $postalCodeField */
        $postalCodeField = $schema->getCurrentVersion()->getField('postalCode');
        $postalCodeField->getValidationRules()->addWarning('postal_code:NL');

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
