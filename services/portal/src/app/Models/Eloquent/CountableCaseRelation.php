<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

interface CountableCaseRelation
{
    public function getCaseUuid(): ?string;

    public function getCaseRelationCount(): int;

    /**
     * @see config.relationcounts.log_threshold
     */
    public function getConfigKey(): string;
}
