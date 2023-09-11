<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property string $user_uuid
 * @property CarbonImmutable $locked_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property EloquentCase $case
 * @property EloquentUser $user
 */
class EloquentCaseLock extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'case_lock';

    protected $fillable = [
        'case_uuid',
        'user_uuid',
        'locked_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function case(): HasOne
    {
        return $this->hasOne(EloquentCase::class, 'uuid', 'case_uuid');
    }

    public function user(): HasOne
    {
        return $this->hasOne(EloquentUser::class, 'uuid', 'user_uuid');
    }
}
