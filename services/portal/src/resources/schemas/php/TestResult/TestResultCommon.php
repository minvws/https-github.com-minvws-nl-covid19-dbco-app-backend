<?php

namespace App\Models\Versions\TestResult;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property string $uuid
 * @property string $messageId
 * @property \App\Models\Versions\Organisation\OrganisationV1 $organisation
 * @property ?\App\Models\Versions\Person\PersonV1 $person
 * @property ?\App\Models\Versions\TestResultRaw\TestResultRawV1 $raw
 * @property \MinVWS\DBCO\Enum\Models\TestResultType $type
 * @property \MinVWS\DBCO\Enum\Models\TestResultSource $source
 * @property ?string $sourceId
 * @property ?string $monsterNumber
 * @property \DateTimeInterface $dateOfTest
 * @property ?\DateTimeInterface $dateOfSymptomOnset
 * @property \App\Models\Versions\TestResult\General\GeneralV1 $general
 * @property \DateTimeInterface $receivedAt
 * @property \DateTimeInterface $createdAt
 * @property \DateTimeInterface $updatedAt
 * @property ?\MinVWS\DBCO\Enum\Models\TestResultTypeOfTest $typeOfTest
 * @property ?string $customTypeOfTest
 * @property \DateTimeInterface $dateOfResult
 * @property ?string $sampleLocation
 * @property \MinVWS\DBCO\Enum\Models\TestResultResult $result
 * @property ?string $laboratory
 */
interface TestResultCommon
{
}

