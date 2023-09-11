<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\UpdateCaseIndexAge;
use App\Models\CovidCase\Index;
use Webmozart\Assert\Assert;

class IndexObserver
{
    public function created(Index $index): void
    {
        $this->updateCaseIndexAge($index);
    }

    public function updated(Index $index): void
    {
        $this->updateCaseIndexAge($index);
    }

    private function updateCaseIndexAge(Index $index): void
    {
        /** @var ?Index $originalIndex */
        $originalIndex = $index->getOriginalFragmentData();
        Assert::nullOrIsInstanceOf($originalIndex, Index::class);

        if ($originalIndex?->dateOfBirth?->format('Y-m-d') === $index->dateOfBirth?->format('Y-m-d')) {
            return;
        }

        UpdateCaseIndexAge::dispatch($index->covidCase->uuid)
            ->afterCommit();
    }
}
