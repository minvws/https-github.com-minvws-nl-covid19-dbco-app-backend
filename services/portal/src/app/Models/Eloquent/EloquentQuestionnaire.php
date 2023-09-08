<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $uuid
 * @property string $name
 * @property string $task_type
 * @property int $version
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class EloquentQuestionnaire extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'questionnaire';
}
