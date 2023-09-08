<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\PolicyGuideline;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class PolicyGuidelineEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var PolicyGuideline $value */
        $container->uuid = $value->uuid;
        $container->policyVersionUuid = $value->policy_version_uuid;
        $container->policyVersionStatus = $value->policyVersion?->status->value;
        $container->identifier = $value->identifier;
        $container->name = $value->name;

        $container->sourceStartDateReference = $value->source_start_date_reference;
        $container->sourceStartDateAddition = $value->source_start_date_addition;

        $container->sourceEndDateReference = $value->source_end_date_reference;
        $container->sourceEndDateAddition = $value->source_end_date_addition;

        $container->contagiousStartDateReference = $value->contagious_start_date_reference;
        $container->contagiousStartDateAddition = $value->contagious_start_date_addition;

        $container->contagiousEndDateReference = $value->contagious_end_date_reference;
        $container->contagiousEndDateAddition = $value->contagious_end_date_addition;
    }
}
