<?php

declare(strict_types=1);

namespace App\Services\CaseUpdate;

use App\Schema\Validation\ValidationResult;
use Throwable;

class CaseUpdateValidationException extends CaseUpdateException
{
    /** @var array<string, ValidationResult> */
    private array $validationResults;

    /**
     * @param array<string, ValidationResult> $validationResults
     */
    public function __construct(string $message, array $validationResults, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->validationResults = $validationResults;
    }

    /**
     * @return array<string, ValidationResult>
     */
    public function getValidationResults(): array
    {
        return $this->validationResults;
    }
}
