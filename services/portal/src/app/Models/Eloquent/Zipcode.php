<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $zipcode
 * @property string $organisation_uuid
 */
class Zipcode extends Model
{
    use CamelCaseAttributes;
    use HasFactory;

    protected $table = 'zipcode';

    public $primaryKey = 'zipcode';
    public $incrementing = false;
    public $timestamps = false;
}
