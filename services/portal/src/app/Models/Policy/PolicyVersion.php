<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Events\PolicyVersionCreated;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Policy\PolicyVersionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;

/**
 * @property string $uuid
 * @property string $name
 * @property PolicyVersionStatus $status
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 *
 * @property Collection<int,CalendarItem> $calenderItem
 * @property Collection<int,PolicyGuideline> $policyGuidelines
 * @property Collection<int,RiskProfile> $riskProfiles
 *
 * @method static PolicyVersionFactory<static> factory()
 */
class PolicyVersion extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'policy_version';

    protected $casts = [
        'start_date' => 'immutable_datetime',
        'status' => PolicyVersionStatus::class,
    ];

    protected $dispatchesEvents = [
        'created' => PolicyVersionCreated::class,
    ];

    protected $fillable = [
        'uuid',
        'name',
        'status',
        'start_date',
    ];

    public function policyGuidelines(): HasMany
    {
        return $this->hasMany(PolicyGuideline::class);
    }

    public function riskProfiles(): HasMany
    {
        return $this->hasMany(RiskProfile::class);
    }

    public function calendarItems(): HasMany
    {
        return $this->hasMany(CalendarItem::class);
    }

    public function calendarViews(): HasMany
    {
        return $this->hasMany(CalendarView::class);
    }

    protected static function newFactory(): PolicyVersionFactory
    {
        return new PolicyVersionFactory();
    }
}
