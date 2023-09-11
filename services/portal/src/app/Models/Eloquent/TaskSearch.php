<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $task_uuid
 * @property string $key
 * @property string $hash
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class TaskSearch extends Model
{
    use HasFactory;

    protected $table = 'task_search';
    protected $fillable = [
        'key',
        'hash',
    ];
}
