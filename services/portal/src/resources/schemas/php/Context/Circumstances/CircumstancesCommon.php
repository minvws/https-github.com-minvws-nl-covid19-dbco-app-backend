<?php

namespace App\Models\Versions\Context\Circumstances;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $isUsingPPE
 * @property ?string $ppeType
 * @property ?array<\MinVWS\DBCO\Enum\Models\PersonalProtectiveEquipment> $usedPersonalProtectiveEquipment
 * @property ?string $ppeReplaceFrequency
 * @property ?bool $ppeMedicallyCompetent
 * @property ?array<\MinVWS\DBCO\Enum\Models\CovidMeasure> $covidMeasures
 * @property ?array<string> $otherCovidMeasures
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $causeForConcern
 * @property ?string $causeForConcernRemark
 * @property ?bool $sharedTransportation
 */
interface CircumstancesCommon
{
}

