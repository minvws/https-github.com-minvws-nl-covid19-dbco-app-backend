<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Contracts\TimelineInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property string $timelineable_type
 * @property string $timelineable_id
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property EloquentCase $case
 * @property TimelineInterface $timelineable
 */
class Timeline extends EloquentBaseModel
{
    protected $table = 'timeline';

    public function timelineable(): MorphTo
    {
        return $this->morphTo();
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }
}
