<?php

declare(strict_types=1);

namespace App\Models\Policy;

use Carbon\CarbonInterface;
use Database\Factories\Eloquent\Policy\CalendarViewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;

/**
 * @property string $uuid
 * @property string $label
 * @property CalendarViewEnum $calendar_view_enum
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @method static CalendarViewFactory<static> factory()
 */
class CalendarView extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'calendar_view';

    protected $casts = [
        'calendar_view_enum' => CalendarViewEnum::class,
    ];

    protected $fillable = [
        'policy_version_uuid',
        'label',
        'calendar_view_enum',
    ];

    public function calendarItems(): BelongsToMany
    {
        return $this->belongsToMany(CalendarItem::class, 'calendar_view_calendar_item', 'calendar_view_uuid', 'calendar_item_uuid');
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(PolicyVersion::class, 'policy_version_uuid', 'uuid');
    }

    protected static function newFactory(): CalendarViewFactory
    {
        return new CalendarViewFactory();
    }
}
