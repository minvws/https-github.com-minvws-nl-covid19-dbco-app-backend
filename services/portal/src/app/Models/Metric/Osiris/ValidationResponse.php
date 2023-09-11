<?php

declare(strict_types=1);

namespace App\Models\Metric\Osiris;

enum ValidationResponse: string
{
    case NotApplicable = 'not_applicable';
    case None = 'none';
    case HasWarnings = 'has_warnings';
    case HasErrors = 'has_errors';
}
