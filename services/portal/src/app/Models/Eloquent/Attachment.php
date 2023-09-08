<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $uuid
 * @property string $file_name
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property ?CarbonInterface $inactive_since
 */
class Attachment extends EloquentBaseModel
{
    use HasFactory;
    use CamelCaseAttributes;

    /** @var string $table */
    protected $table = 'attachment';

    protected $casts = [
        'inactive_since' => 'datetime:Y-m-d H:i:s',
    ];
}
