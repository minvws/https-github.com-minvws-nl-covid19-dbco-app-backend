<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

/**
 * @property string $uuid
 * @property string $expert_question_uuid
 * @property ?CarbonImmutable $case_created_at
 * @property string $answer
 * @property string $answered_by
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property EloquentUser $answeredBy
 * @property ExpertQuestion $question
 */
class ExpertQuestionAnswer extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'expert_question_answer';

    protected $casts = [
        'answer' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
    ];

    protected $fillable = [
        'expert_question_uuid',
        'case_created_at',
        'answer',
        'answered_by',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExpertQuestion::class, 'expert_question_uuid', 'uuid');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'answered_by');
    }
}
