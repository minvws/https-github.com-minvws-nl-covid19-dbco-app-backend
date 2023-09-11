<?php

declare(strict_types=1);

namespace App\Schema\Update;

use App\Schema\Validation\ValidationResult;

class UpdateValidationException extends UpdateException
{
    private ValidationResult $validationResult;

    public function __construct(string $message, ValidationResult $validationResult)
    {
        parent::__construct($message);

        $this->validationResult = $validationResult;
    }

    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }
}
