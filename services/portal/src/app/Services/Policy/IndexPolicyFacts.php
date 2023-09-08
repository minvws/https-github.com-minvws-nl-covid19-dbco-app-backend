<?php

declare(strict_types=1);

namespace App\Services\Policy;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

final class IndexPolicyFacts
{
    public ?CarbonImmutable $dateOfSymptomOnset = null;
    public ?CarbonImmutable $dateOfTest = null;

    private function __construct(
        public readonly ?YesNoUnknown $hasSymptoms,
        public readonly ?YesNoUnknown $isImmunoCompromised,
        public readonly ?YesNoUnknown $isHospitalAdmitted,
        public readonly ?HospitalReason $hospitalReason,
    ) {
    }

    public static function create(
        ?YesNoUnknown $hasSymptoms,
        ?YesNoUnknown $isImmunoCompromised,
        ?YesNoUnknown $isHospitalAdmitted,
        ?HospitalReason $hospitalReason,
    ): IndexPolicyFacts {
        return new self($hasSymptoms, $isImmunoCompromised, $isHospitalAdmitted, $hospitalReason);
    }

    public function withDateOfSymptomOnset(DateTimeInterface $dateOfSymptomOnset): IndexPolicyFacts
    {
        $facts = clone $this;
        $facts->dateOfSymptomOnset = CarbonImmutable::instance($dateOfSymptomOnset);
        return $facts;
    }

    public function withDateOfTest(DateTimeInterface $testDate): IndexPolicyFacts
    {
        $facts = clone $this;
        $facts->dateOfTest = CarbonImmutable::instance($testDate);
        return $facts;
    }
}
