<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Communication\CommunicationV3Up;

use function assert;
use function is_string;
use function trim;

class MELGGDExternBuilder extends AbstractSingleValueBuilder
{
    /**
     * RIVM Osiris requires this answer for `DELETED_STATUS` type messages.
     * If remarks are not set, we return a static remarks value instead.
     * Should in time this implementation not meet RIVM requirements,
     * add a schema validation rule for this type of notification.
     */
    private const DELETED_CASE_STATIC_RIVM_REMARK = 'Verwijderd';

    protected function getValue(EloquentCase $case): ?string
    {
        $value = $this->getRemarksRivmValue($case);

        return $value ?? ($case->trashed() ? self::DELETED_CASE_STATIC_RIVM_REMARK : $value);
    }

    private function getRemarksRivmValue(EloquentCase $case): ?string
    {
        assert(isset($case->communication));

        if (!$case->communication instanceof CommunicationV3Up) {
            return null;
        }

        if (!is_string($case->communication->remarksRivm)) {
            return null;
        }

        return trim($case->communication->remarksRivm) === '' ? null : $case->communication->remarksRivm;
    }
}
