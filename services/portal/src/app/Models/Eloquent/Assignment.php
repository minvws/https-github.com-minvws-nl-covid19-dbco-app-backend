<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Versions\Context\ContextCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $uuid
 * @property string $chore_uuid
 * @property string $user_uuid
 * @property ?CarbonImmutable $expires_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property CarbonImmutable $deleted_at
 *
 * @property Chore $chore
 * @property EloquentUser $user
 */
class Assignment extends EloquentBaseModel implements SchemaObject, SchemaProvider, ContextCommon
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;
    use SoftDeletes;

    protected $table = 'assignment';
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('active', static function (Builder $builder): void {
            $builder->whereNull('assignment.expires_at')
                ->orWhere('assignment.expires_at', '>', CarbonImmutable::now());
        });
    }

    public function chore(): BelongsTo
    {
        return $this->belongsTo(Chore::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class);
    }
}
