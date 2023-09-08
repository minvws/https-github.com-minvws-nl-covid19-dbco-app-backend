<?php

declare(strict_types=1);

namespace App\Schema\Validation;

use Illuminate\Contracts\Validation\Validator;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

/**
 * Results for multi-level validation.
 */
class ValidationResult implements Encodable
{
    public const FATAL = ValidationRules::FATAL;
    public const WARNING = ValidationRules::WARNING;
    public const NOTICE = ValidationRules::NOTICE;

    /** @var array */
    private array $data;

    private bool $valid = true;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @var array<Validator> */
    private array $validators = [];

    /**
     * Data has no errors?
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Data was still considered valid after validation at the given level?
     */
    public function isLevelValid(string $level): bool
    {
        $validator = $this->getValidator($level);
        return $validator !== null && !$validator->fails();
    }

    /**
     * Returns the validator instance for the given level.
     */
    public function getValidator(string $level): ?Validator
    {
        return $this->validators[$level] ?? null;
    }

    /**
     * Returns the validated data for the given level.
     *
     * Returns data for the fields that validated successfully.
     *
     * @return array
     */
    public function validated(string $level): array
    {
        $validator = $this->getValidator($level);
        if ($validator === null) {
            return [];
        }

        return $validator->validated();
    }

    /**
     * Adds the validator instance for the given level.
     *
     * @param string $level Validation level.
     * @param Validator $validator Validator instance with the validation results.
     */
    public function add(string $level, Validator $validator): void
    {
        $this->validators[$level] = $validator;

        if ($validator->fails()) {
            $this->valid = false;
        }
    }

    public function encode(EncodingContainer $container): void
    {
        foreach ($this->validators as $level => $validator) {
            if (!$validator->fails()) {
                continue;
            }

            $container->$level->failed = $validator->failed();
            $container->$level->errors = $validator->errors();
        }
    }
}
