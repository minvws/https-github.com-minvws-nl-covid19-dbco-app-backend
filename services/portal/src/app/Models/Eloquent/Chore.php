<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Versions\Context\ContextCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $uuid
 * @property string $organisation_uuid
 * @property string $resource_type
 * @property string $resource_id
 * @property string $resource_permission
 * @property string $owner_resource_type
 * @property string $owner_resource_id
 * @property ?CarbonImmutable $expires_at
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property ?CarbonInterface $deleted_at
 *
 * @property Assignment $assignment
 * @property EloquentOrganisation $organisation
 * @property Resource $resource
 * @property Resource $ownerResource
 */
class Chore extends EloquentBaseModel implements SchemaObject, SchemaProvider, ContextCommon
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;
    use SoftDeletes;

    protected $table = 'chore';

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'resource' => 'resource',
        'owner_resource' => 'ownerResource',
    ];

    protected $hidden = [
        'resource_type',
        'resource_id',
        'owner_resource_type',
        'owner_resource_id',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    public function resourceable(): MorphTo
    {
        return $this->morphTo('resource');
    }

    public function ownerResourceable(): MorphTo
    {
        return $this->morphTo('owner_resource');
    }

    public function getResourceAttribute(): Resource
    {
        return new Resource($this->resource_type, $this->resource_id);
    }

    public function getOwnerResourceAttribute(): Resource
    {
        return new Resource($this->owner_resource_type, $this->owner_resource_id);
    }

    public function hasAssignment(): bool
    {
        return (bool) $this->assignment;
    }
}
