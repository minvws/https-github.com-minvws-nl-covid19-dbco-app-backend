<?php

declare(strict_types=1);

namespace App\Models\Metric;

interface Metric
{
    public function getName(): string;

    public function getHelp(): string;

    /**
     * return array<int|string, int|string>
     */
    public function getLabels(): array;
}
