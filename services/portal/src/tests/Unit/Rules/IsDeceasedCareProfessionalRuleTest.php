<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\IsDeceasedCareProfessionalRule;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\TestCase;

class IsDeceasedCareProfessionalRuleTest extends TestCase
{
    public function testRuleValidationPasses(): void
    {
        $validator = Validator::make(['isDeceased' => YesNoUnknown::no()->value], [
            'isDeceased' => new IsDeceasedCareProfessionalRule(),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleValidationFails(): void
    {
        $validator = Validator::make(['isDeceased' => YesNoUnknown::yes()->value], [
            'isDeceased' => new IsDeceasedCareProfessionalRule(),
        ]);

        $this->assertTrue($validator->fails());
    }

    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['isDeceased' => ''], [
            'isDeceased' => new IsDeceasedCareProfessionalRule(),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['isDeceased' => YesNoUnknown::yes()], [
            'isDeceased' => new IsDeceasedCareProfessionalRule(),
        ]);

        $this->assertTrue($validator->fails());
    }
}
