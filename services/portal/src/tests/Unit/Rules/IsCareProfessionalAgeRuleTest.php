<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsCareProfessionalAgeRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class IsCareProfessionalAgeRuleTest extends TestCase
{
    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['dateOfBirth' => ''], [
            'dateOfBirth' => new IsCareProfessionalAgeRule(true, 65, 'isAfter', 'message'),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['dateOfBirth' => CarbonImmutable::parse('today')], [
            'dateOfBirth' => new IsCareProfessionalAgeRule(true, 65, 'isAfter', 'message'),
        ]);

        $this->assertTrue($validator->fails());
    }
}
