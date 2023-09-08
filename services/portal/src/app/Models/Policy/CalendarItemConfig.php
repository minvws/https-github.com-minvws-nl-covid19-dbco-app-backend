<?php

declare(strict_types=1);

namespace App\Models\Policy;

use Database\Factories\Eloquent\Policy\CalendarItemConfigFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $policy_guideline_uuid
 * @property string $calendar_item_uuid
 * @property bool $is_hidden
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 *
 * @property PolicyGuideline $policyGuideline
 * @property CalendarItem $calendarItem
 * @property EloquentCollection<array-key,CalendarItemConfigStrategy> $calendarItemConfigStrategies
 *
 * @method static CalendarItemConfigFactory<static> factory()
 */
class CalendarItemConfig extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'calendar_item_config';

    protected $casts = [
        'is_hidden' => 'bool',
    ];

    protected $fillable = [
        'uuid',
        'policy_guideline_uuid',
        'calendar_item_uuid',
        'is_hidden',
    ];

    public function calendarItem(): BelongsTo
    {
        return $this->belongsTo(CalendarItem::class);
    }

    /**
     * @codeCoverageIgnore
     */
    public function policyGuideline(): BelongsTo
    {
        return $this->belongsTo(PolicyGuideline::class);
    }

    public function calendarItemConfigStrategies(): HasMany
    {
        return $this->hasMany(CalendarItemConfigStrategy::class);
    }

    protected static function newFactory(): CalendarItemConfigFactory
    {
        return new CalendarItemConfigFactory();
    }
}
