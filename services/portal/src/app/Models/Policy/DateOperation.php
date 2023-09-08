<?php

declare(strict_types=1);

namespace App\Models\Policy;

use App\Casts\OriginDateCast;
use Database\Factories\Eloquent\Policy\DateOperationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\DateOperationRelativeDay;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\UnitOfTime;

use function abs;
use function assert;
use function intval;
use function is_array;

/**
 * @property string $uuid
 * @property string $calendar_item_config_uuid
 * @property DateOperationIdentifier $identifier_type
 * @property DateOperationMutation $mutation_type
 * @property int $amount
 * @property UnitOfTime $unit_of_time_type
 * @property IndexOriginDate|ContactOriginDate $origin_date_type
 * @property DateOperationRelativeDay $relative_day
 *
 * @property CalendarItemConfigStrategy $calendarItemConfigStrategy
 *
 * @method static DateOperationFactory<static> factory()
 */
class DateOperation extends EloquentBaseModel
{
    use HasFactory;

    protected $table = 'date_operation';

    protected $casts = [
        'identifier_type' => DateOperationIdentifier::class,
        'mutation_type' => DateOperationMutation::class,
        'unit_of_time_type' => UnitOfTime::class,
        'origin_date_type' => OriginDateCast::class,
    ];

    protected $fillable = [
        'uuid',
        'calendar_item_config_strategy_uuid',
        'identifier_type',
        'mutation_type',
        'amount',
        'unit_of_time_type',
        'origin_date_type',
        'relative_day',
    ];

    /**
     * @codeCoverageIgnore
     */
    public function calendarItemConfigStrategy(): BelongsTo
    {
        return $this->belongsTo(CalendarItemConfigStrategy::class);
    }

    public function getLoadedCalendarItem(): ?CalendarItem
    {
        if (
            $this->relationLoaded('calendarItemConfigStrategy')
            && $this->calendarItemConfigStrategy->relationLoaded('calendarItemConfig')
            && $this->calendarItemConfigStrategy->calendarItemConfig->relationLoaded('calendarItem')
        ) {
            return $this->calendarItemConfigStrategy->calendarItemConfig->calendarItem;
        }

        return null;
    }

    protected static function newFactory(): DateOperationFactory
    {
        return new DateOperationFactory();
    }

    protected function relativeDay(): Attribute
    {
        return Attribute::make(
            get: static function (mixed $value, mixed $attributes): DateOperationRelativeDay {
                assert(is_array($attributes));

                $output = 0;
                if ($attributes['amount'] > 0 && $attributes['mutation_type'] === DateOperationMutation::sub()->value) {
                    $output = 0 - $attributes['amount'];
                }

                if ($attributes['amount'] > 0 && $attributes['mutation_type'] === DateOperationMutation::add()->value) {
                    $output = 0 + $attributes['amount'];
                }

                return DateOperationRelativeDay::from($output);
            },
            set: static function (DateOperationRelativeDay $value) {
                $amount = intval($value->value);

                $mutationType = DateOperationMutation::add();
                if ($amount < 0) {
                    $mutationType = DateOperationMutation::sub();
                }

                return [
                    'amount' => abs($amount),
                    'mutation_type' => $mutationType,
                    'unit_of_time_type' => UnitOfTime::day(),
                ];
            },
        );
    }
}
