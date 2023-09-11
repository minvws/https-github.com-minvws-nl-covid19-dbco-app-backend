<?php

namespace App\Models\Versions\CovidCase;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property string $uuid
 * @property ?string $pseudoId
 * @property ?string $caseId
 * @property ?string $hpzoneNumber
 * @property \App\Models\Versions\Organisation\OrganisationV1 $organisation
 * @property ?\App\Models\Versions\Organisation\OrganisationV1 $assignedOrganisation
 * @property ?\App\Models\Versions\CaseList\CaseListV1 $assignedCaseList
 * @property ?\App\Models\Versions\User\UserV1 $assignedUser
 * @property \MinVWS\DBCO\Enum\Models\BCOStatus $bcoStatus
 * @property ?\MinVWS\DBCO\Enum\Models\BCOPhase $bcoPhase
 * @property ?\MinVWS\DBCO\Enum\Models\IndexStatus $indexStatus
 * @property ?\App\Models\Versions\User\UserV1 $createdBy
 * @property \DateTimeInterface $createdAt
 * @property \DateTimeInterface $updatedAt
 * @property ?\DateTimeInterface $completedAt
 * @property ?\DateTimeInterface $deletedAt
 * @property \MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus $automaticAddressVerificationStatus
 * @property \App\Models\Versions\CovidCase\Abroad\AbroadV1 $abroad
 * @property \App\Models\Versions\CovidCase\AlternateContact\AlternateContactV1 $alternateContact
 * @property \App\Models\Versions\CovidCase\Contact\ContactV1 $contact
 * @property \App\Models\Versions\CovidCase\Deceased\DeceasedV1 $deceased
 * @property \App\Models\Versions\CovidCase\Job\JobV1 $job
 * @property \App\Models\Versions\CovidCase\Hospital\HospitalV1 $hospital
 * @property \App\Models\Versions\CovidCase\Housemates\HousematesV1 $housemates
 * @property \App\Models\Versions\CovidCase\AlternativeLanguage\AlternativeLanguageV1 $alternativeLanguage
 * @property \App\Models\Versions\CovidCase\AlternateResidency\AlternateResidencyV1 $alternateResidency
 * @property \App\Models\Versions\CovidCase\GeneralPractitioner\GeneralPractitionerV1 $generalPractitioner
 * @property \App\Models\Versions\CovidCase\PrincipalContextualSettings\PrincipalContextualSettingsV1 $principalContextualSettings
 * @property \App\Models\Versions\CovidCase\GroupTransport\GroupTransportV1 $groupTransport
 * @property \App\Models\Versions\CovidCase\Medication\MedicationV1 $medication
 * @property ?int $osirisNumber
 * @property array<\App\Models\Versions\CaseUpdate\CaseUpdateV1> $caseUpdates
 * @property array<\App\Models\Versions\Context\ContextV1> $contexts
 */
interface CovidCaseCommon
{
}

