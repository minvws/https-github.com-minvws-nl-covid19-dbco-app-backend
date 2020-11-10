<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EloquentBaseModel extends Model
{
    protected $primaryKey = "uuid";
    protected $keyType = "string";
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->{$model->getKeyName()} === null) {
                $model->{$model->getKeyName()} = (string)Str::uuid();
            }
        });
    }
}
