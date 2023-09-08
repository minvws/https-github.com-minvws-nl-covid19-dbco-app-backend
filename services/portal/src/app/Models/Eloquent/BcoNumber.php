<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $uuid
 * @property string $bco_number
 */
class BcoNumber extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'bco_numbers';

    public $timestamps = false;

    public $fillable = ['bco_number'];
}
