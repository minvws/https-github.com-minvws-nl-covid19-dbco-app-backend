<?php

declare(strict_types=1);

namespace App\Models\Metric\DataDisclosure;

use App\Models\Metric\CounterMetric;

final class InvalidCertificate extends CounterMetric
{
    protected string $name = 'data_disclosure_invalid_certificate';
    protected string $help = 'Counts the total number of failed login attempts by invalid certificate';
}
