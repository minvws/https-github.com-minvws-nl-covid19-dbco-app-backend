<?php

declare(strict_types=1);

namespace App\Models\Shared;

use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Shared\Address\AddressCommon;
use App\Schema\CachesSchema;
use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;

use function app;

class Address extends Entity implements SchemaProvider, AddressCommon
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Shared\\Address');
        $schema->setDocumentationIdentifier('shared.address');

        static::addDefaultValidationFields($schema);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public static function addDefaultValidationFields(Schema $schema): void
    {
        $schema->add(StringType::createField('postalCode'))->getValidationRules()->addFatal('max:10');
        $schema->add(StringType::createField('houseNumber'))->getValidationRules()->addFatal('max:10');
        $schema->add(StringType::createField('houseNumberSuffix'))->getValidationRules()->addFatal('max:10');
        $schema->add(StringType::createField('street'))->getValidationRules()->addFatal('max:250');
        $schema->add(StringType::createField('town'))->getValidationRules()->addFatal('max:250');
    }
}
