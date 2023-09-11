<?php

namespace App\Models\Versions\CovidCase\Test;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property ?\DateTimeInterface $dateOfSymptomOnset
 * @property ?bool $isSymptomOnsetEstimated
 * @property ?\DateTimeInterface $dateOfTest
 * @property ?\DateTimeInterface $dateOfResult
 * @property ?\DateTimeInterface $dateOfInfectiousnessStart
 * @property ?string $otherReason
 * @property ?\MinVWS\DBCO\Enum\Models\InfectionIndicator $infectionIndicator
 * @property ?\MinVWS\DBCO\Enum\Models\SelfTestIndicator $selfTestIndicator
 * @property ?\MinVWS\DBCO\Enum\Models\LabTestIndicator $labTestIndicator
 * @property ?string $otherLabTestIndicator
 * @property ?string $monsterNumber
 * @property ?\DateTimeInterface $selfTestLabTestDate
 * @property ?\MinVWS\DBCO\Enum\Models\TestResult $selfTestLabTestResult
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $isReinfection
 * @property ?\DateTimeInterface $previousInfectionDateOfSymptom
 * @property ?bool $previousInfectionSymptomFree
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $previousInfectionProven
 * @property ?bool $contactOfConfirmedInfection
 * @property ?\MinVWS\DBCO\Enum\Models\YesNoUnknown $previousInfectionReported
 * @property ?string $source
 * @property ?string $testLocation
 * @property ?string $testLocationCategory
 */
interface TestCommon
{
}

