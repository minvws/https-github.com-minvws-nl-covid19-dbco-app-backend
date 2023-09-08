<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\RiskProfile;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class RiskProfileEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var RiskProfile $value */
        $container->uuid = $value->uuid;
        $container->policyVersionUuid = $value->policy_version_uuid;
        $container->policyGuidelineUuid = $value->policy_guideline_uuid;
        $container->name = $value->name;
        $container->personTypeEnum = $value->person_type_enum->value;
        $container->riskProfileEnum = $value->risk_profile_enum->value;
        $container->isActive = $value->is_active;
        $container->sortOrder = $value->sort_order;
    }
}
