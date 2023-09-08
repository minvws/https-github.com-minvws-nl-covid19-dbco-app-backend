<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $uuid
 * @property string $name
 * @property ?string $hpzone_number
 * @property ?string $alarm
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $snoozed_at
 *
 * @property Collection<int, EloquentCase> $cases
 * @property Collection<int, Place> $places
 */
class EloquentSituation extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'situation';

    protected $casts = [
        'datetime' => 'snoozed_at',
    ];

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'situation_place', 'situation_uuid', 'place_uuid');
    }

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(EloquentCase::class, 'situation_case', 'situation_uuid', 'case_uuid');
    }
}
