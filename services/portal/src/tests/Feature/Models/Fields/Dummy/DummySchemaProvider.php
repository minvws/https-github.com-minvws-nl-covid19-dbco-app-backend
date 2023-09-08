<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Fields\Dummy;

use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;

class DummySchemaProvider extends EloquentBaseModel implements SchemaObject, SchemaProvider
{
    use HasSchema;

    public static function getSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setDocumentationIdentifier('DummySchemaProvider');
        $schema->setUseVersionedClasses(true);
        $schema->setCurrentVersion(1);

        $schema->add(StringType::createField('fieldWithPurpose'))
            ->specifyPurpose(
                static fn(PurposeSpecificationBuilder $builder) => $builder->addPurpose(Purpose::ScientificResearch, SubPurpose::Linking)
            );

        $schema->add(StringType::createField('fieldWithoutPurpose'));

        return $schema;
    }
}
