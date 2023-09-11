<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function is_array;

class EloquentBaseModel extends Model
{
    /**
     * @var string|array<string> $primaryKey
     *
     * @phpstan-ignore-next-line
     */
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model): void {
            $pk = $model->getKeyName();
            if (is_array($pk) || $pk !== 'uuid') {
                return;
            }

            // Automatically populate the uuid primary key if this is a uuid-key
            // based model.
            if ($model->{$pk} === null) { // unless the model already did this
                $model->{$pk} = (string) Str::uuid();
            }
        });
    }
}
