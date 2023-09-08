<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Dummy;

use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\CachesSchema;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use Illuminate\Database\Eloquent\Model;

class DummyModelA extends Model implements SchemaProvider
{
    use CachesSchema;

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);
        $schema->setCurrentVersion(1);

        $schema->add(StringType::createField('qualityofcare'))->specifyPurpose(
            static fn(PurposeSpecificationBuilder $builder) => $builder->addPurpose(Purpose::QualityOfCare, SubPurpose::EpiCurve)
        );

        $schema->add(StringType::createField('scientificResearch'))->specifyPurpose(
            static fn(PurposeSpecificationBuilder $builder) => $builder->addPurpose(
                Purpose::ScientificResearch,
                SubPurpose::InterpretEpiCurve,
            )
        );

        $schema->add(StringType::createField('overlap'))->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
            $builder->addPurpose(Purpose::ScientificResearch, SubPurpose::InterpretEpiCurve);
            $builder->addPurpose(Purpose::QualityOfCare, SubPurpose::EpiCurve);
        });

        return $schema;
    }
}
