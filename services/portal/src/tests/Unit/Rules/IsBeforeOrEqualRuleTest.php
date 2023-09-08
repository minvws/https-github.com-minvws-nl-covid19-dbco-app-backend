<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsBeforeOrEqualRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IsBeforeOrEqualRuleTest extends TestCase
{
    public function testRuleValidationPassesOnDateInPast(): void
    {
        $validator = Validator::make(['foo' => '-1 year'], [
            'foo' => new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Date should be before today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationPassesOnSameDate(): void
    {
        $validator = Validator::make(['foo' => 'today'], [
            'foo' => new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Date should be before today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationFails(): void
    {
        $validator = Validator::make(['foo' => '1 year'], [
            'foo' => new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Date should be before today.'),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => ''], [
            'foo' => new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Date should be before today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => CarbonImmutable::parse('today')], [
            'foo' => new IsBeforeOrEqualRule(CarbonImmutable::parse('today'), 'Date should be before today.'),
        ]);

        $this->assertTrue($validator->fails());
    }
}
