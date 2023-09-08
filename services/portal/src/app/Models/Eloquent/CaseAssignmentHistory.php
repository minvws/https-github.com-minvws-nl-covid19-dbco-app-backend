<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Contracts\TimelineInterface;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Scopes\OrganisationAuthScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $uuid
 * @property string $covidcase_uuid
 * @property ?string $assigned_user_uuid
 * @property ?string $assigned_organisation_uuid
 * @property ?string $assigned_case_list_uuid
 * @property CarbonInterface $assigned_at
 * @property ?string $assigned_by
 * @property ?string $assigned_case_list_name
 *
 * @property ?EloquentUser $assignedBy
 * @property EloquentCase $case
 * @property ?CaseList $list
 * @property ?EloquentOrganisation $organisation
 * @property ?EloquentUser $user
 * @property Timeline $timeline
 */
class CaseAssignmentHistory extends EloquentBaseModel implements TimelineInterface
{
    use HasFactory;
    use CamelCaseAttributes;

    protected $table = 'case_assignment_history';

    public const COLUMN_CASE_UUID = 'covidcase_uuid';

    public $with = ['user', 'organisation', 'list', 'assignedBy'];

    public $timestamps = false;

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    protected $fillable = [
        'covidcase_uuid',
        'assigned_user_uuid',
        'assigned_organisation_uuid',
        'assigned_case_list_uuid',
        'assigned_case_list_name',
        'assigned_at',
        'assigned_by',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, self::COLUMN_CASE_UUID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'assigned_user_uuid');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'assigned_by');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'assigned_organisation_uuid')
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(CaseList::class, 'assigned_case_list_uuid');
    }

    public function timeline(): MorphOne
    {
        return $this->morphOne(Timeline::class, 'timelineable');
    }

    public function hasUser(): bool
    {
        return $this->assigned_user_uuid !== null;
    }

    public function hasOrganisation(): bool
    {
        return $this->assigned_organisation_uuid !== null;
    }

    public function hasList(): bool
    {
        return $this->assigned_case_list_uuid !== null;
    }

    public function getCaseUuid(): string
    {
        return $this->covidcase_uuid;
    }
}
