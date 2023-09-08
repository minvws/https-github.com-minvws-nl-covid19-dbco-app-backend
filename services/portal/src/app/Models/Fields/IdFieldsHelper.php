<?php

declare(strict_types=1);

namespace App\Models\Fields;

use App\Models\Purpose\Purpose;
use App\Models\Purpose\SubPurpose;
use App\Schema\Fields\PseudonomizedField;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\Types\UUIDType;
use MinVWS\Codable\EncodingContext;

use function array_search;

class IdFieldsHelper
{
    public static function addIdFieldsToSchema(Schema $schema, string $idFieldName = 'uuid', string $pseudoIdFieldName = 'pseudoId'): void
    {
        $schema->add(UUIDType::createField($idFieldName))
            ->setAllowsNull(false)
            ->setIncludedInEncode(false, EncodingContext::MODE_EXPORT);

        $schema->add(PseudonomizedField::createFromField($pseudoIdFieldName, $idFieldName))
            ->setIncludedInEncode(false)
            ->setIncludedInEncode(true, EncodingContext::MODE_EXPORT)
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                //Exclude the ToBeDetermined case from the purpose
                $cases = Purpose::cases();
                unset($cases[array_search(Purpose::ToBeDetermined, $cases, true)]);
                $builder->addPurposes($cases, SubPurpose::Linking);
            });
    }
}
