<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $context_uuid
 * @property CarbonImmutable $day
 * @property ?string $start_time
 * @property ?string $end_time
 *
 * @property Context $context
 */
class Moment extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'moment';
    protected $casts = [
        'day' => 'date',
    ];
    public $timestamps = false;

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }
}
