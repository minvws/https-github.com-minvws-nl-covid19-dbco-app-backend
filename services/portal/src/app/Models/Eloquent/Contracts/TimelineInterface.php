<?php

declare(strict_types=1);

namespace App\Models\Eloquent\Contracts;

use App\Models\Eloquent\Timeline;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Timeline $timeline
 */
interface TimelineInterface
{
    public function timeline(): MorphOne;

    public function getCaseUuid(): string;
}
