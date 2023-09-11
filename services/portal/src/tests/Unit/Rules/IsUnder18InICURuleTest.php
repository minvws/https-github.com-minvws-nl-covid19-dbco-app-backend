<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsUnder18InICURule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;

class IsUnder18InICURuleTest extends TestCase
{
    public function testRuleValidationPassesWhenInICU(): void
    {
        $validator = Validator::make(['foo' => YesNoUnknown::yes()->value], [
            'foo' => new IsUnder18InICURule(CarbonImmutable::parse('-20 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationPassesWhenNotInICU(): void
    {
        $validator = Validator::make(['foo' => YesNoUnknown::no()->value], [
            'foo' => new IsUnder18InICURule(CarbonImmutable::parse('-20 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationFails(): void
    {
        $validator = Validator::make(['foo' => YesNoUnknown::yes()->value], [
            'foo' => new IsUnder18InICURule(CarbonImmutable::parse('-10 years')),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => null], [
            'foo' => new IsUnder18InICURule(CarbonImmutable::parse('-10 years')),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => YesNoUnknown::yes()], [
            'foo' => new IsUnder18InICURule(CarbonImmutable::parse('-10 years')),
        ]);

        $this->assertTrue($validator->fails());
    }
}
