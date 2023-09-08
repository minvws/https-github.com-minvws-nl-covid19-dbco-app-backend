<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Contracts\TimelineInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

/**
 * @property string $uuid
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property CarbonInterface $case_created_at
 * @property string $case_uuid
 * @property string $user_uuid
 * @property ?string $assigned_user_uuid
 * @property ExpertQuestionType $type
 * @property string $subject
 * @property ?string $phone
 * @property string $question
 *
 * @property ?ExpertQuestionAnswer $answer
 * @property ?EloquentUser $assignedUser
 * @property EloquentCase $case
 * @property EloquentUser $user
 * @property ?TimelineInterface $timeline
 */
class ExpertQuestion extends EloquentBaseModel implements TimelineInterface
{
    use HasFactory;

    protected $table = 'expert_question';

    protected $casts = [
        'type' => ExpertQuestionType::class,
        'subject' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'phone' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'question' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
    ];

    protected $fillable = [
        'case_uuid',
        'user_uuid',
        'assigned_user_uuid',
        'case_created_at',
        'type',
        'phone',
        'subject',
        'question',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_uuid');
    }

    public function answer(): HasOne
    {
        return $this->hasOne(ExpertQuestionAnswer::class, 'expert_question_uuid', 'uuid');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'assigned_user_uuid');
    }

    public function available(): bool
    {
        return $this->assignedUser === null || $this->assigned_user_uuid === Auth::id();
    }

    public function timeline(): MorphOne
    {
        return $this->morphOne(Timeline::class, 'timelineable');
    }

    public function getCaseUuid(): string
    {
        return $this->case_uuid;
    }

    public function hasAnswer(): bool
    {
        return $this->answer !== null;
    }

    public function hasAssignment(): bool
    {
        return $this->assigned_user_uuid !== null;
    }
}
