<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Casts\CalendarItemColor;
use App\Events\CalendarItemCreated;
use App\Repositories\Policy\PopulatorReferenceEnum;
use Carbon\CarbonInterface;
use Database\Factories\Eloquent\Policy\CalendarItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

use function is_null;

/**
 * @property string $uuid
 * @property string $policy_version_uuid
 * @property PolicyPersonType $person_type_enum
 * @property CalendarItemEnum $calendar_item_enum
 * @property string $label
 * @property FixedCalendarItem $fixed_calendar_item_enum
 * @property CalendarPointColor|CalendarPeriodColor $color_enum
 * @property ?PopulatorReferenceEnum $populator_reference_enum
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property PolicyVersion $policyVersion
 *
 * @method static CalendarItemFactory<static> factory()
 */
final class CalendarItem extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'calendar_item';

    protected $casts = [
        'person_type_enum' => PolicyPersonType::class,
        'calendar_item_enum' => CalendarItemEnum::class,
        'fixed_calendar_item_enum' => FixedCalendarItem::class,
        'color_enum' => CalendarItemColor::class,
        'populator_reference_enum' => PopulatorReferenceEnum::class,
    ];

    protected $fillable = [
        'policy_version_uuid',
        'person_type_enum',
        'calendar_item_enum',
        'label',
        'fixed_calendar_item_enum',
        'color_enum',
        'populator_reference_enum',
    ];

    protected $dispatchesEvents = [
        'created' => CalendarItemCreated::class,
    ];

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(PolicyVersion::class);
    }

    public function isHideable(): bool
    {
        return is_null($this->fixed_calendar_item_enum);
    }

    public function isDeletable(): bool
    {
        return is_null($this->fixed_calendar_item_enum);
    }

    /**
     * @codeCoverageIgnore
     */
    public function calendarViews(): BelongsToMany
    {
        return $this->belongsToMany(CalendarView::class, 'calendar_view_calendar_item', 'calendar_item_uuid', 'calendar_view_uuid');
    }

    protected static function newFactory(): CalendarItemFactory
    {
        return new CalendarItemFactory();
    }
}
