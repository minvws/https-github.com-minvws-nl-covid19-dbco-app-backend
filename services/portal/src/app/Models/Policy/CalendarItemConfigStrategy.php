<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Casts\CalendarItemConfigStrategyIdentifierCast;
use App\Events\CalendarItemConfigStrategyCreated;
use App\Events\CalendarItemConfigStrategyUpdated;
use Database\Factories\Eloquent\Policy\CalendarItemConfigStrategyFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\CalendarItemConfigStrategyIdentifierType;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;

/**
 * @property string $uuid
 * @property string $calendar_item_config_uuid
 * @property CalendarItemConfigStrategyIdentifierType $identifier_type
 * @property PointCalendarStrategyType|PeriodCalendarStrategyType $strategy_type
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property CalendarItemConfig $calendarItemConfig
 * @property EloquentCollection<array-key,DateOperation> $dateOperations
 *
 * @method static CalendarItemConfigStrategyFactory<static> factory()
 */
class CalendarItemConfigStrategy extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'calendar_item_config_strategy';

    protected $casts = [
        'identifier_type' => CalendarItemConfigStrategyIdentifierType::class,
        'strategy_type' => CalendarItemConfigStrategyIdentifierCast::class,
    ];

    protected $fillable = [
        'uuid',
        'calendar_item_config_uuid',
        'identifier_type',
        'strategy_type',
    ];

    protected $dispatchesEvents = [
        'created' => CalendarItemConfigStrategyCreated::class,
        'updated' => CalendarItemConfigStrategyUpdated::class,
    ];

    public function calendarItemConfig(): BelongsTo
    {
        return $this->belongsTo(CalendarItemConfig::class);
    }

    public function dateOperations(): HasMany
    {
        return $this->hasMany(DateOperation::class);
    }

    public function getLoadedCalendarItem(): ?CalendarItem
    {
        if ($this->relationLoaded('calendarItemConfig') && $this->calendarItemConfig->relationLoaded('calendarItem')) {
            return $this->calendarItemConfig->calendarItem;
        }

        return null;
    }

    protected static function newFactory(): CalendarItemConfigStrategyFactory
    {
        return new CalendarItemConfigStrategyFactory();
    }
}
