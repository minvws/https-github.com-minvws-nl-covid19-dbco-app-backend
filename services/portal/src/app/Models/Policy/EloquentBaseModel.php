<?php

declare(strict_types=1);

namespace App\Models\Policy;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as EloquentBuilder;
use Illuminate\Support\Str;

/**
 * @mixin EloquentBuilder
 * @mixin QueryBuilder
 */
class EloquentBaseModel extends Model
{
    use HasUuids;

    protected $primaryKey = 'uuid';

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }
}
