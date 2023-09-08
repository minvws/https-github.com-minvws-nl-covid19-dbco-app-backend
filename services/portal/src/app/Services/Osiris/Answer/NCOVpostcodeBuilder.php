<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Index\IndexV1;
use App\Models\Versions\CovidCase\Index\IndexV2;
use MinVWS\DBCO\Enum\Models\NoBsnOrAddressReason;

use function count;
use function in_array;
use function is_null;
use function preg_match;
use function strtoupper;

class NCOVpostcodeBuilder extends AbstractSingleValueBuilder
{
    private const REGEX_VALID_POSTAL_CODE = '/^(\d{4}[A-Z]{2})$/i';

    private const HOMELESS = '001';
    private const FOREIGN_TRAVELER = '009';
    private const UNKNOWN = '008';

    protected function getValue(EloquentCase $case): ?string
    {
        if (($case->index instanceof IndexV1) && $case->index->hasNoBsnOrAddress === true) {
            return self::UNKNOWN;
        }

        if ($case->index instanceof IndexV2) {
            if (!is_null($case->index->hasNoBsnOrAddress) && count($case->index->hasNoBsnOrAddress) > 0) {
                if (in_array(NoBsnOrAddressReason::homeless(), $case->index->hasNoBsnOrAddress, true)) {
                    return self::HOMELESS;
                }

                if (in_array(NoBsnOrAddressReason::foreignPasserby(), $case->index->hasNoBsnOrAddress, true)) {
                    return self::FOREIGN_TRAVELER;
                }

                return self::UNKNOWN;
            }
        }

        $postalCode = $case->index->address?->postalCode;
        if ($postalCode === null || !preg_match(self::REGEX_VALID_POSTAL_CODE, $postalCode)) {
            return self::UNKNOWN;
        }

        return strtoupper($postalCode);
    }
}
