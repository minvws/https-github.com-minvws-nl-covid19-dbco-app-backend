<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\Validation;

use App\Schema\Traits\ValidationTagging;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\Priority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('validation')]
class ValidationTaggingTest extends UnitTestCase
{
    use ValidationTagging;

    #[DataProvider('validationRulesDataProvider')]
    public function testValidationRuleFiltering(array $inputRules, array $filterTags, array $expectedOutputRules): void
    {
        $validationRules = new ValidationRules();
        $validationRulesChild = new ValidationRules();
        $key = 'dummyChildKey';
        $validationRules->addChild($validationRulesChild, $key);

        foreach ($inputRules as $inputRule => $tags) {
            $validationRulesChild->addWarning($inputRule, $tags);
        }

        $rules = [];

        $context = new ValidationContext();
        $context->setLevel(ValidationContext::WARNING);
        $rules[ValidationContext::WARNING] = $validationRules->make($context);

        $mappedAndFilteredRules = $this->mapRules($rules, $filterTags);

        $this->assertArrayHasKey(ValidationContext::WARNING, $mappedAndFilteredRules);
        $this->assertEquals($expectedOutputRules, $mappedAndFilteredRules[ValidationContext::WARNING][$key]);
    }

    public static function validationRulesDataProvider(): array
    {
        return [
            'Singe rule' => [
                ['required' => []],
                [],
                ['required'],
            ],
            'Singe rule without tag filtered' => [
                ['required' => []],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [],
            ],
            'Single rule with Osiris final tag filtered' => [
                ['required' => [ValidationRule::TAG_OSIRIS_FINAL]],
                [ValidationRule::TAG_OSIRIS_FINAL],
                ['required'],
            ],
            'Single rule with multiple tags filtered' => [
                ['required' => [ValidationRule::TAG_OSIRIS_FINAL, ValidationRule::TAG_OSIRIS_INITIAL]],
                [ValidationRule::TAG_OSIRIS_FINAL],
                ['required'],
            ],
            'Single rule with tag filtered by other tag' => [
                ['required' => [ValidationRule::TAG_OSIRIS_FINAL]],
                [ValidationRule::TAG_OSIRIS_INITIAL],
                [],
            ],
            'Single rule filtered by multiple tags' => [
                ['required' => [ValidationRule::TAG_OSIRIS_FINAL]],
                [ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL],
                ['required'],
            ],
            'Multiple rules without tags' => [
                ['required' => [], 'string' => []],
                [],
                ['required', 'string'],
            ],
            'Multiple rules without tag filtered' => [
                ['required' => [], 'string' => []],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [],
            ],
            'Multiple rules without tag filtered with value' => [
                ['required' => [], 'foo' => [ValidationRule::TAG_OSIRIS_FINAL]],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [1 => 'foo'],
            ],
            'Multiple rules and nullable' => [
                ['required' => [], 'foo' => [ValidationRule::TAG_OSIRIS_FINAL], 'nullable' => [ValidationRule::TAG_ALWAYS]],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [1 => 'foo', 2 => 'nullable'],
            ],
        ];
    }

    #[DataProvider('validationRulesMultiDataProvider')]
    public function testValidationRuleFilteringMulti(array $inputRules, array $tags, array $filterTags, array $expectedOutputRules): void
    {
        $validationRules = new ValidationRules();
        $validationRulesChild = new ValidationRules();
        $key = 'dummyChildKey';
        $validationRules->addChild($validationRulesChild, $key);

        foreach ($inputRules as $inputRule) {
            $validationRulesChild->addWarning($inputRule, $tags);
        }

        $rules = [];

        $context = new ValidationContext();
        $context->setLevel(ValidationContext::WARNING);
        $rules[ValidationContext::WARNING] = $validationRules->make($context);

        $mappedAndFilteredRules = $this->mapRules($rules, $filterTags);

        $this->assertArrayHasKey(ValidationContext::WARNING, $mappedAndFilteredRules);
        $this->assertEquals($expectedOutputRules, $mappedAndFilteredRules[ValidationContext::WARNING][$key]);
    }

    public static function validationRulesMultiDataProvider(): array
    {
        $closureRule = static function ($context) {
            return 'required';
        };

        return [
            'Multiple rules in Array' => [
                [['required', 'string']],
                [],
                [],
                ['required', 'string'],
            ],
            'Multiple rules in Array with Osiris final tag' => [
                [['required', 'string']],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [],
                ['required', 'string'],
            ],
            'Multiple rules in Array with Osiris final tag and filtered' => [
                [['required', 'string']],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [ValidationRule::TAG_OSIRIS_FINAL],
                ['required', 'string'],
            ],
            'Multiple rules in Array with Osiris final tag and filtered by multiple' => [
                [['required', 'string']],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [ValidationRule::TAG_OSIRIS_FINAL, Validationrule::TAG_OSIRIS_INITIAL],
                ['required', 'string'],
            ],
            'Multiple rules in Array with Osiris final tag and filtered by other tag' => [
                [['required', 'string']],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [ValidationRule::TAG_OSIRIS_INITIAL],
                [],
            ],
            'Multiple rules without tag filtered' => [
                [['required'], ['string']],
                [],
                [ValidationRule::TAG_OSIRIS_FINAL],
                [],
            ],
            'Closure rule' => [
                [$closureRule],
                [],
                [],
                ['required'],
            ],
        ];
    }

    public function testValidationRuleObject(): void
    {
        $validationRules = new ValidationRules();
        $validationRulesChild = new ValidationRules();
        $key = 'dummyChildKey';
        $validationRules->addChild($validationRulesChild, $key);

        $ruleObject = Rule::in(Priority::allValues());
        $validationRulesChild->addWarning($ruleObject);

        $rules = [];

        $context = new ValidationContext();
        $context->setLevel(ValidationContext::WARNING);
        $rules[ValidationContext::WARNING] = $validationRules->make($context);

        $mappedAndFilteredRules = $this->mapRules($rules, []);

        $this->assertArrayHasKey(ValidationContext::WARNING, $mappedAndFilteredRules);
        $this->assertEquals($ruleObject, $mappedAndFilteredRules[ValidationContext::WARNING][$key][0]);
    }
}
