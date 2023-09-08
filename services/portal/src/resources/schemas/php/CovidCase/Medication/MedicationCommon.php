<?php

namespace App\Models\Versions\CovidCase\Medication;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $hasMedication
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $isImmunoCompromised
 * @property ?string $immunoCompromisedRemarks
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $hasGivenPermission
 * @property ?string $practitioner
 * @property ?string $practitionerPhone
 * @property ?string $hospitalName
 * @property ?array<\App\Models\Versions\CovidCase\Medication\MedicineV1> $medicines
 */
interface MedicationCommon
{
}

