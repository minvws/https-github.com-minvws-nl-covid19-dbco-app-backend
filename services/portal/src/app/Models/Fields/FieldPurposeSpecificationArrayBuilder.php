<?php

declare(strict_types=1);

namespace App\Models\Fields;

use App\Models\Purpose\CSVPurpose;
use App\Models\Purpose\Purpose;
use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Purpose\PurposeSpecificationBuilder;

use function array_map;

class FieldPurposeSpecificationArrayBuilder
{
    /**
     * @return array<string, array<string,PurposeSpecification>>
     */
    public static function build(array $data): array
    {
        $purposes = [];
        foreach ($data as $purposeSpecification) {
            if (!isset($purposeSpecification[0]) || !isset($purposeSpecification[1])) {
                continue;
            }

            //trim all values in purpose specification
            $purposeSpecification = array_map('trim', $purposeSpecification);
            [$class, $field] = $purposeSpecification;

            $builder = new PurposeSpecificationBuilder();

            $array = [
                6 => Purpose::EpidemiologicalSurveillance,
                7 => Purpose::QualityOfCare,
                8 => Purpose::AdministrativeAdvice,
                9 => Purpose::OperationalAdjustment,
                10 => Purpose::ScientificResearch,
                11 => Purpose::ToBeDetermined,
            ];

            foreach ($array as $key => $purpose) {
                if (!isset($purposeSpecification[$key]) || $purposeSpecification[$key] === '') {
                    continue;
                }

                $subPurpose = CSVPurpose::fromCSVString($purposeSpecification[$key]);
                if (!isset($subPurpose)) {
                    continue;
                }

                $builder->addPurpose($purpose, $subPurpose);
            }

            $builder->setRemark($purposeSpecification[5]);

            $purposes[$class][$field] = $builder->build();
        }

        return $purposes;
    }
}
