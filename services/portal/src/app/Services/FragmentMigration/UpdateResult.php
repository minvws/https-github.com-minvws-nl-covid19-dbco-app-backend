<?php

declare(strict_types=1);

namespace App\Services\FragmentMigration;

final class UpdateResult
{
    private int $updatedCount;
    private int $skippedCount;

    public function __construct(int $updatedCount, int $skippedCount)
    {
        $this->updatedCount = $updatedCount;
        $this->skippedCount = $skippedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
