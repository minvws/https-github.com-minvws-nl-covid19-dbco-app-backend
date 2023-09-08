<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Contracts\TimelineInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $uuid
 * @property string $subject
 * @property string $description
 * @property ?string $created_by
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property Chore $chore
 * @property ?EloquentUser $createdBy
 * @property Timeline $timeline
 */
class CallToAction extends EloquentBaseModel implements TimelineInterface
{
    use HasFactory;

    protected $table = 'call_to_action';

    protected $fillable = [
        'subject',
        'description',
    ];

    public function chore(): MorphOne
    {
        return $this->morphOne(Chore::class, 'owner_resource');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'created_by');
    }

    public function timeline(): MorphOne
    {
        return $this->morphOne(Timeline::class, 'timelineable');
    }

    public function getCaseUuid(): string
    {
        return $this->chore->resource_id ?? '';
    }
}
