<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $task_uuid
 * @property string $question_uuid
 * @property ?string $spv_value
 * @property ?string $ctd_firstname
 * @property ?string $ctd_lastname
 * @property ?string $ctd_email
 * @property ?string $ctd_phonenumber
 * @property ?string $cfd_cat_1_risk
 * @property ?string $cfd_cat_2a_risk
 * @property ?string $cfd_cat_2b_risk
 * @property ?string $cfd_cat_3_risk
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property EloquentTask $task
 * @property EloquentQuestion $question
 */
class EloquentAnswer extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'answer';

    public function task(): BelongsTo
    {
        return $this->belongsTo(EloquentTask::class, 'task_uuid');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EloquentQuestion::class, 'question_uuid');
    }
}
