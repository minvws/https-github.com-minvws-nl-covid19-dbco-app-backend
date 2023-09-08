<?php

namespace App\Models\Versions\CovidCase\Hospital;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $isAdmitted
 * @property ?string $name
 * @property ?string $location
 * @property ?\DateTimeInterface $admittedAt
 * @property ?\DateTimeInterface $releasedAt
 * @property ?\MinVWS\DBCO\Enum\Models\HospitalReason $reason
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $hasGivenPermission
 * @property ?string $practitioner
 * @property ?string $practitionerPhone
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $isInICU
 * @property ?\DateTimeInterface $admittedInICUAt
 */
interface HospitalCommon
{
}

