<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsAfterOrEqualRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IsAfterOrEqualRuleTest extends TestCase
{
    public function testRuleValidationPasses(): void
    {
        $validator = Validator::make(['foo' => '1 year'], [
            'foo' => new IsAfterOrEqualRule(CarbonImmutable::parse('today'), 'Date should be after or equal today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationPassesOnSameDate(): void
    {
        $validator = Validator::make(['foo' => 'today'], [
            'foo' => new IsAfterOrEqualRule(CarbonImmutable::parse('today'), 'Date should be after or equal today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationFails(): void
    {
        $validator = Validator::make(['foo' => '-1 year'], [
            'foo' => new IsAfterOrEqualRule(CarbonImmutable::parse('today'), 'Date should be after or equal  today.'),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => ''], [
            'foo' => new IsAfterOrEqualRule(CarbonImmutable::parse('today'), 'Date should be after or equal  today.'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['foo' => CarbonImmutable::parse('today')], [
            'foo' => new IsAfterOrEqualRule(CarbonImmutable::parse('today'), 'Date should be after or equal  today.'),
        ]);

        $this->assertTrue($validator->fails());
    }
}
