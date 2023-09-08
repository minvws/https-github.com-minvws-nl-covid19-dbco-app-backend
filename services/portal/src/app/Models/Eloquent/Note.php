<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Contracts\TimelineInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\CaseNoteType;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property ?string $user_uuid
 * @property string $user_name
 * @property ?string $organisation_name
 * @property ?CaseNoteType $type
 * @property string $note
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property CarbonInterface $case_created_at
 *
 * @property EloquentCase $case
 * @property Timeline $timeline
 * @property ?EloquentUser $user
 */
class Note extends EloquentBaseModel implements TimelineInterface
{
    use HasFactory;

    protected $table = 'note';

    public string $caseUuidColumn = 'case_uuid';

    protected $casts = [
        'note' => Sealed::class . ':' . StorageTerm::LONG . ',case_created_at',
        'type' => CaseNoteType::class,
    ];

    protected $fillable = [
        'case_created_at',
        'note',
        'user_name',
        'organisation_name',
        'type',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, $this->caseUuidColumn);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'user_uuid');
    }

    public function timeline(): MorphOne
    {
        return $this->morphOne(Timeline::class, 'timelineable');
    }

    public function getCaseUuid(): string
    {
        return $this->case_uuid;
    }
}
