<?php

declare(strict_types=1);

namespace App\Services\Policy\PolicyVersion;

use Carbon\CarbonInterface;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;

class PolicyVersionStatusTransitionValidator
{
    public function isValid(PolicyVersionStatus $fromStatus, PolicyVersionStatus $toStatus, CarbonInterface $startDate): bool
    {
        // Allow status transitions which don't change the status
        if ($fromStatus === $toStatus) {
            return true;
        }

        // On the current day the only allowed a status transition is to active
        if (
            $startDate->isToday() && (
                $fromStatus === PolicyVersionStatus::draft() || // manual activation
                $fromStatus === PolicyVersionStatus::activeSoon() // scheduled activation
            )
        ) {
            return $toStatus === PolicyVersionStatus::active();
        }

        // On future days we only allow status transition to activeSoon or back to draft
        if ($fromStatus === PolicyVersionStatus::draft()) {
            return $toStatus === PolicyVersionStatus::activeSoon();
        }

        if ($fromStatus === PolicyVersionStatus::activeSoon()) {
            return $toStatus === PolicyVersionStatus::draft();
        }

        // We only allow a status transition from draft or activeSoon
        return false;
    }
}
