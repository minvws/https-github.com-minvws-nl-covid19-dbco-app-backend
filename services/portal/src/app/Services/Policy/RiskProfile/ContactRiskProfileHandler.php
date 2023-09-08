<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Exceptions\Policy\UnsupportedPolicyFactObjectHandlerException;
use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function in_array;

final class ContactRiskProfileHandler implements RiskProfileHandler
{
    /**
     * @param array<ContactCategory> $acceptedContactCategories
     */
    public function __construct(
        private readonly PolicyGuidelineHandler $policyGuidelineHandler,
        public array $acceptedContactCategories,
        public YesNoUnknown $acceptedImmunity,
        public YesNoUnknown $acceptedCloseContactDuringQuarantine,
    ) {
    }

    public function getPolicyGuidelineHandler(): PolicyGuidelineHandler
    {
        return $this->policyGuidelineHandler;
    }

    public function isApplicable(ContactPolicyFacts|IndexPolicyFacts $facts): bool
    {
        if (!$facts instanceof ContactPolicyFacts) {
            throw new UnsupportedPolicyFactObjectHandlerException();
        }

        return in_array($facts->contactCategory, $this->acceptedContactCategories, true)
            && $facts->immunity === $this->acceptedImmunity
            && $facts->closeContactDuringQuarantine === $this->acceptedCloseContactDuringQuarantine;
    }
}
