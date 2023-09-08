<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $place_uuid
 * @property int $index_count
 * @property int $index_count_since_reset
 * @property ?CarbonImmutable $last_index_presence
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Place $place
 */
class PlaceCounters extends EloquentBaseModel
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasUuids;

    protected $primaryKey = 'place_uuid';
    protected $fillable = [
        'index_count',
        'index_count_since_reset',
        'last_index_presence',
    ];
    protected $casts = [
        'last_index_presence' => 'datetime:Y-m-d',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
