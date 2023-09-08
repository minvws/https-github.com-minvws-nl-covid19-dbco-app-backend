<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsUnder4AdmittedAtHospitalRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;

class IsUnder4AdmittedAtHospitalTest extends TestCase
{
    public function testRuleValidationPassesWhenAdmittedAtHospital(): void
    {
        $validator = Validator::make(['isAdmitted' => YesNoUnknown::yes()->value], [
            'isAdmitted' => new IsUnder4AdmittedAtHospitalRule(CarbonImmutable::parse('-8 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationPassesWhenNotAdmittedAtHospital(): void
    {
        $validator = Validator::make(['isAdmitted' => YesNoUnknown::no()->value], [
            'isAdmitted' => new IsUnder4AdmittedAtHospitalRule(CarbonImmutable::parse('-8 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationFails(): void
    {
        $validator = Validator::make(['isAdmitted' => YesNoUnknown::yes()->value], [
            'isAdmitted' => new IsUnder4AdmittedAtHospitalRule(CarbonImmutable::parse('-2 years')),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['isAdmitted' => null], [
            'isAdmitted' => new IsUnder4AdmittedAtHospitalRule(CarbonImmutable::parse('-2 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['isAdmitted' => YesNoUnknown::yes()], [
            'isAdmitted' => new IsUnder4AdmittedAtHospitalRule(CarbonImmutable::parse('-2 years')),
        ]);

        $this->assertTrue($validator->fails());
    }
}
