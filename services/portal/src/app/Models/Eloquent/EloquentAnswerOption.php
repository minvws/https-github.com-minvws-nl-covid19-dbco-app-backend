<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;

/**
 * @property string $uuid
 * @property string $question_uuid
 * @property string $label
 * @property string $value
 * @property ?string $trigger_name
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property int $sort_order
 */
class EloquentAnswerOption extends EloquentBaseModel
{
    protected $table = 'answer_option';
}
