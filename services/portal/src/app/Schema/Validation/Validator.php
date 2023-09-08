<?php

declare(strict_types=1);

namespace App\Schema\Validation;

use App\Schema\Traits\ValidationTagging;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator as IlluminateValidator;

/**
 * Validator that supports validating multiple levels of validation rules retrieved
 * from a validation rules container.
 */
class Validator
{
    use ValidationTagging;

    public const FATAL = ValidationRules::FATAL;
    public const WARNING = ValidationRules::WARNING;
    public const NOTICE = ValidationRules::NOTICE;

    private ValidationContext $context;

    private ValidationRules $rules;

    /** @var array<string> */
    private array $levels = [ValidationRules::FATAL, ValidationRules::WARNING, ValidationRules::NOTICE];

    private bool $excludeUnvalidatedArrayKeys = true;

    /**
     * @param ValidationContext|null $context Optional context, will be instantiated automatically if not provided.
     * @param ValidationRules|null $rules Optional rules container, will be instantiated automatically if not provided.
     */
    public function __construct(?ValidationContext $context = null, ?ValidationRules $rules = null)
    {
        $this->context = $context ?? new ValidationContext();
        $this->rules = $rules ?? new ValidationRules();
    }

    /**
     * Validation context.
     */
    public function getContext(): ValidationContext
    {
        return $this->context;
    }

    /**
     * Validation rules container.
     */
    public function getValidationRules(): ValidationRules
    {
        return $this->rules;
    }

    /**
     * The validation levels.
     *
     * Defaults to: FATAL, WARNING, NOTICE.
     *
     * @return array<string>
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * @param array<string> $levels
     */
    public function setLevels(array $levels): void
    {
        $this->levels = $levels;
    }

    /**
     * Strip unvalidated array keys from the validated data.
     */
    public function setExcludeUnvalidatedArrayKeys(bool $excludeUnvalidatedArrayKeys): void
    {
        $this->excludeUnvalidatedArrayKeys = $excludeUnvalidatedArrayKeys;
    }

    public function validate(array $data): ValidationResult
    {
        $result = new ValidationResult($data);

        foreach ($this->levels as $level) {
            $this->getContext()->setLevel($level);
            $rules = $this->getValidationRules()->make($this->getContext());
            $mappedRules = $this->mapRules($rules);

            $validator = ValidatorFacade::make($data, $mappedRules);
            if ($validator instanceof IlluminateValidator) {
                $validator->excludeUnvalidatedArrayKeys = $this->excludeUnvalidatedArrayKeys;
            }

            $result->add($level, $validator);
            if ($validator->fails()) {
                break;
            }
        }

        return $result;
    }
}
