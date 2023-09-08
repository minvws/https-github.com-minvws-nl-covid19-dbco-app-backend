<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $uuid
 * @property ?string $identifier
 * @property string $questionnaire_uuid
 * @property string $group_name
 * @property string $question_type
 * @property string $label
 * @property ?string $description
 * @property string $relevant_for_categories
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?string $header
 * @property int $sort_order
 * @property ?string $hpzone_fieldref
 */
class EloquentQuestion extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'question';
}
