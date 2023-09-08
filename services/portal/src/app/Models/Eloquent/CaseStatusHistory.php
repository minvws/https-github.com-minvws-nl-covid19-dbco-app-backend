<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Enum\Models\BCOStatus;

/**
 * @property string $uuid
 * @property string $covidcase_uuid
 * @property BCOStatus $bco_status
 * @property CarbonImmutable $changed_at
 *
 * @property EloquentCase $case
 */
class CaseStatusHistory extends EloquentBaseModel
{
    use HasFactory;
    use CamelCaseAttributes;

    protected $table = 'case_status_history';

    protected $casts = [
        'bco_status' => BCOStatus::class,
    ];

    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        self::creating(static fn (CaseStatusHistory $model) => $model->changed_at = $model->freshTimestamp());
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'covidcase_uuid', 'uuid');
    }
}
