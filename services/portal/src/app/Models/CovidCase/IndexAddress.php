<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Shared\Address;
use App\Models\Versions\CovidCase\IndexAddress\IndexAddressCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Fields\Field;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\SchemaType;
use App\Schema\Validation\ValidationContext;

use function app;

class IndexAddress extends Entity implements SchemaProvider, IndexAddressCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase\\IndexAddress');
        $schema->setDocumentationIdentifier('covidCase.indexAddress');

        Address::addDefaultValidationFields($schema);

        /** @var Field<SchemaType> $postalCodeField */
        $postalCodeField = $schema->getVersion(1)->getField('postalCode');
        $postalCodeField->getValidationRules()
            ->addWarning(
                static fn (ValidationContext $context): array =>
                    $context->getValue('postalCode') ? ['postal_code:NL'] : [],
            );

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }
}
