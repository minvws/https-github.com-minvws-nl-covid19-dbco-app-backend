<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\CounterMetric;

final class CaseExportFailed extends CounterMetric
{
    protected string $name = 'osiris:case_export:failed';
    protected string $help = 'Counts the total number of failed attempts to export a case to Osiris';

    private function __construct(string $status, ValidationResponse $validationResponse)
    {
        $this->labels = ['status' => $status, 'validation_response' => $validationResponse->value];
    }

    public static function rejected(ValidationResponse $validationResponse): self
    {
        return new self('rejected', $validationResponse);
    }

    public static function failed(): self
    {
        return new self('failed', ValidationResponse::NotApplicable);
    }
}
