<?php

namespace App\Models\Versions\Intake;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property string $uuid
 * @property \MinVWS\DBCO\Enum\Models\IntakeType $type
 * @property string $source
 * @property string $identifierType
 * @property string $identifier
 * @property string $pseudoBsnGuid
 * @property ?int $cat1Count
 * @property ?int $estimatedCat2Count
 * @property ?string $firstname
 * @property ?string $prefix
 * @property ?string $lastname
 * @property \DateTimeInterface $dateOfBirth
 * @property ?\DateTimeInterface $dateOfSymptomOnset
 * @property \DateTimeInterface $dateOfTest
 * @property \DateTimeInterface $receivedAt
 * @property \DateTimeInterface $createdAt
 * @property ?array<\App\Models\Versions\IntakeFragment\IntakeFragmentV1> $fragments
 * @property ?array<\App\Models\Versions\IntakeContact\IntakeContactV1> $contacts
 * @property string $pc3
 * @property \MinVWS\DBCO\Enum\Models\Gender $gender
 */
interface IntakeCommon
{
}

