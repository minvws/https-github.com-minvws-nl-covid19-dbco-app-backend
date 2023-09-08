<?php

declare(strict_types=1);

namespace App\Models\CovidCase;

use App\Helpers\Config;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\CaseLabel;
use App\Scopes\CaseListAuthScope;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use MinVWS\Codable\Codable;
use MinVWS\Codable\CodingKey;
use MinVWS\Codable\CodingKeys;
use MinVWS\Codable\DateTimeFormatException;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\Priority;

use function app;
use function sprintf;

/**
 * @deprecated use \App\Models\Eloquent\EloquentCase, see DBCO-3004
 */
class PlannerCase implements Codable, Validatable
{
    use CodingKeys;

    public ?string $uuid = null;
    public ?string $assignedCaseListUuid = null;
    public ?string $pseudoBsnGuid = null;
    public ?General $general = null;
    public ?Index $index = null;
    public ?Contact $contact = null;
    public ?Test $test = null;
    public ?string $label = null;
    public ?int $priority = null;
    public AutomaticAddressVerificationStatus $automaticAddressVerificationStatus;

    public function __construct()
    {
        $this->automaticAddressVerificationStatus = AutomaticAddressVerificationStatus::unchecked();
    }

    /** @var array<CaseLabel> $caseLabels */
    public array $caseLabels = [];

    protected static function modifyCodingKey(CodingKey $key): void
    {
        if ($key->getName() === 'caseLabels') {
            $key->elementType(CaseLabel::class);
        }
    }

    public static function createValidationRules(array $data): array
    {
        $rules = self::validationRules($data);
        $fatalRules = $rules[self::SEVERITY_LEVEL_FATAL];
        $requiredFields = ['index.firstname', 'index.lastname', 'index.dateOfBirth', 'contact.phone'];
        foreach ($requiredFields as $field) {
            $fatalRules[$field] = "required|" . $fatalRules[$field];
        }
        $fatalRules['general.reference'][] = 'prohibited';
        $fatalRules['general.hpzoneNumber'][] = Rule::unique('covidcase', 'hpzone_number')->whereNull('deleted_at');
        $fatalRules['test.monsterNumber'][] = Rule::unique('covidcase', 'test_monster_number')->whereNull('deleted_at');
        $rules[self::SEVERITY_LEVEL_FATAL] = $fatalRules;
        return $rules;
    }

    /**
     * @inheritDoc
     *
     * Note: Only the submitted data is validated, the data from the db is not merged in the validation stage.
     * Won't refactor for now, because in the future, CoronIt is going to create the cases, and this will be removed
     */
    public static function validationRules(array $data): array
    {
        $rules = [];

        $maxBeforeCaseCreationDate = CarbonImmutable::now()
            ->sub(sprintf('%d days', Config::integer('misc.validations.maxBeforeCaseCreationDateInDays')))
            ->format('Y-m-d');
        $rules[self::SEVERITY_LEVEL_FATAL] = [
            'assignedCaseListUuid' => [
                'nullable',
                'uuid',
                app(CaseListAuthScope::class)->applyToExistsRule(
                    Rule::exists('case_list', 'uuid'),
                ),
            ],
            'general' => 'nullable',
            'general.hpzoneNumber' => ['digits_between:7,8', 'nullable'],
            'general.notes' => 'nullable|string|max:5000',
            'index.initials' => 'nullable|string|max:250',
            'index.firstname' => 'string|max:250',
            'index.lastname' => 'string|max:500',
            'index.dateOfBirth' => sprintf(
                'nullable|date_format:Y-m-d|before:today|after_or_equal:%s',
                Config::string('misc.validations.firstAllowableDateOfBirth'),
            ),
            'index.address.postalCode' => 'nullable|string|max:10',
            'index.address.houseNumber' => 'nullable|string|max:25',
            'index.address.houseNumberSuffix' => 'nullable|string|max:25',
            'index.address.street' => 'nullable|string|max:500',
            'index.address.town' => 'nullable|string|max:500',
            'contact.phone' => 'string|max:25|phone:INTERNATIONAL,NL',
            'contact.email' => 'nullable|string|email|max:250',
            'test.dateOfTest' => 'nullable|date_format:Y-m-d|before_or_equal:today|after_or_equal:' . $maxBeforeCaseCreationDate,
            'test.monsterNumber' => [
                'nullable',
                'string',
                'max:16',
                'regex:/^\d{3}[a-zA-Z]\d{1,12}$/',

            ],
            'label' => 'nullable|string|max:16',
            'pseudoBsnGuid' => [
                'nullable',
                'string',
                'uuid',
            ],
            'priority' => [
                Rule::in(Priority::allValues()),
            ],
            'caseLabels' => 'array',
            'caseLabels.*' => [
                sprintf('exists:%s,uuid', CaseLabel::class),
            ],
        ];

        $rules[self::SEVERITY_LEVEL_WARNING] = [
            'index.address.postalCode' => 'nullable|string|postal_code:NL',
            'index.address.houseNumber' => 'nullable|numeric',
        ];

        return $rules;
    }

    public static function updateValidationRules(array $data, ?string $currentUuid): array
    {
        $rules = self::validationRules($data);
        $fatalRules = $rules[self::SEVERITY_LEVEL_FATAL];
        $fatalRules['general.reference'][] = Rule::unique('covidcase', 'case_id')->ignore(
            $currentUuid,
            'uuid',
        ); // These validation rules are also used when fetching data. So it can't be prohibited for existing cases
        $fatalRules['general.hpzoneNumber'][] = Rule::unique('covidcase', 'hpzone_number')->whereNull('deleted_at')->ignore(
            $currentUuid,
            'uuid',
        );
        $fatalRules['test.monsterNumber'][] = Rule::unique('covidcase', 'test_monster_number')->whereNull('deleted_at')->ignore(
            $currentUuid,
            'uuid',
        );
        $rules[self::SEVERITY_LEVEL_FATAL] = $fatalRules;
        return $rules;
    }

    public static function fetchValidationRules(array $data): array
    {
        $rules = self::validationRules($data);
        $fatalRules = $rules[self::SEVERITY_LEVEL_FATAL];
        unset($fatalRules['caseLabels']);
        unset($fatalRules['caseLabels.*']);
        $rules[self::SEVERITY_LEVEL_FATAL] = $fatalRules;
        return $rules;
    }

    /**
     * Custom encode of the child fragments so we can only return the data the owner is allowed to see.
     */
    public function encode(EncodingContainer $container): void
    {
        if (isset($this->uuid)) {
            $container->uuid = $this->uuid;
        }

        if (isset($this->assignedCaseListUuid)) {
            $container->assignedCaseListUuid = $this->assignedCaseListUuid;
        }

        if (isset($this->label)) {
            $container->label = $this->label;
        }

        if (isset($this->pseudoBsnGuid)) {
            $container->pseudoBsnGuid = $this->pseudoBsnGuid;
        }

        if (isset($this->priority)) {
            $container->priority = $this->priority;
        }

        $container->automaticAddressVerificationStatus = $this->automaticAddressVerificationStatus;

        if (isset($this->general)) {
            $this->encodeGeneral($this->general, $container->nestedContainer('general'));
        }

        if (isset($this->index)) {
            $this->encodeIndex($this->index, $container->nestedContainer('index'));
        }

        if (isset($this->contact)) {
            $this->encodeContact($this->contact, $container->nestedContainer('contact'));
        }

        if (isset($this->test)) {
            $this->encodeTest($this->test, $container->nestedContainer('test'));
        }

        $container->caseLabels = $this->caseLabels;
    }

    /**
     * Encode general fragment.
     */
    private function encodeGeneral(General $general, EncodingContainer $container): void
    {
        $container->reference = $general->reference;
        $container->hpzoneNumber = $general->hpzoneNumber;
        $container->organisation = $general->organisation;
        $container->notes = $general->notes;
    }

    /**
     * Encode index fragment.
     *
     * @throws DateTimeFormatException
     */
    private function encodeIndex(Index $index, EncodingContainer $container): void
    {
        $container->initials = $index->initials;
        $container->firstname = $index->firstname;
        $container->lastname = $index->lastname;
        $container->dateOfBirth->encodeDateTime($index->dateOfBirth, 'Y-m-d');
        $container->address = $index->address;
        $container->bsnCensored = $index->bsnCensored;
        $container->bsnLetters = $index->bsnLetters;
    }

    /**
     * Encode contact fragment.
     */
    private function encodeContact(Contact $contact, EncodingContainer $container): void
    {
        $container->phone = $contact->phone;
        $container->email = $contact->email;
    }

    /**
     * Encode test fragment.
     *
     * @throws DateTimeFormatException
     */
    private function encodeTest(Test $test, EncodingContainer $container): void
    {
        $container->dateOfTest->encodeDateTime($test->dateOfTest, 'Y-m-d');
        $container->monsterNumber = $test->monsterNumber;
    }
}
