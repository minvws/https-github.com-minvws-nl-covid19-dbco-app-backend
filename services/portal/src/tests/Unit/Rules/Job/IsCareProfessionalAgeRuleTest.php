<?php

declare(strict_types=1);

namespace Tests\Unit\Rules\Job;

use App\Rules\Job\IsCareProfessionalAgeRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use MinVWS\DBCO\Enum\Models\JobSector;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class IsCareProfessionalAgeRuleTest extends TestCase
{
    public function testRuleIsInvalidatedWhenNoValueIsGiven(): void
    {
        $validator = Validator::make(['sectors' => ''], [
            'sectors' => new IsCareProfessionalAgeRule(
                CarbonImmutable::instance($this->faker->dateTimeBetween('-64 years', '-17 years')),
                65,
                'isAfter',
                'message',
            ),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleIsInvalidatedWhenDateOfBirthIsNull(): void
    {
        $validator = Validator::make(['sectors' => [JobSector::ziekenhuis()->value]], [
            'sectors' => new IsCareProfessionalAgeRule(
                null,
                65,
                'isAfter',
                'message',
            ),
        ]);

        $this->assertFalse($validator->fails());
    }

    public function testRuleFailsWhenInvalidValueIsGiven(): void
    {
        $validator = Validator::make(['sectors' => JobSector::horeca()], [
            'sectors' => new IsCareProfessionalAgeRule(
                CarbonImmutable::instance($this->faker->dateTimeBetween('-64 years', '-17 years')),
                65,
                'isAfter',
                'message',
            ),
        ]);

        $this->assertTrue($validator->fails());
    }
}
