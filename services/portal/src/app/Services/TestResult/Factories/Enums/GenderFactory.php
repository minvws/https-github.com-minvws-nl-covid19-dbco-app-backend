<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Enums;

use App\Dto\TestResultReport\Gender;
use LogicException;
use MinVWS\DBCO\Enum\Models\Gender as GenderEnum;

use function sprintf;

final class GenderFactory
{
    public static function create(Gender $gender): GenderEnum
    {
        if ($gender->isMale()) {
            return GenderEnum::male();
        }

        if ($gender->isFemale()) {
            return GenderEnum::female();
        }

        if ($gender->isNotSpecified() || $gender->isUnknown()) {
            return GenderEnum::other();
        }

        throw new LogicException(
            sprintf(
                'Failed to instantiate "%s" from "%s"',
                GenderEnum::class,
                $gender::class,
            ),
        );
    }
}
