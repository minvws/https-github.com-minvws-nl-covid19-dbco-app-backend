<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

use App\Models\Metric\CounterMetric;

final class CaseExportSucceeded extends CounterMetric
{
    protected string $name = 'osiris:case_export:succeeded';
    protected string $help = 'Counts the total number of cases successfully exported to Osiris';

    private function __construct(
        ValidationResponse $validationResponse,
    )
    {
        $this->labels = [
            'validation_response' => $validationResponse->value,
            'initial_export' => 'false',
        ];
    }

    public function initial(): CaseExportSucceeded
    {
        $this->labels['initial_export'] = 'true';
        return $this;
    }

    public static function withWarnings(): self
    {
        return new self(ValidationResponse::HasWarnings);
    }

    public static function withoutWarnings(): self
    {
        return new self(ValidationResponse::None);
    }
}
