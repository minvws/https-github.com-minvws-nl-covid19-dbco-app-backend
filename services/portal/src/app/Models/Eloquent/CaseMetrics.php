<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property CarbonImmutable $date
 * @property string $organisation_uuid
 * @property int $created_count
 * @property int $archived_count
 * @property CarbonImmutable $refreshed_at
 *
 * @property EloquentOrganisation $organisation
 */
class CaseMetrics extends Model
{
    use HasFactory;

    protected $table = 'case_metrics';

    protected $casts = [
        'date' => 'datetime',
        'refreshed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid', 'uuid');
    }
}
