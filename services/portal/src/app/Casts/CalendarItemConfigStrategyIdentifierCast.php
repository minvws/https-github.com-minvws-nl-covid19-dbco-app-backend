<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use Webmozart\Assert\Assert;

use function is_null;
use function is_string;
use function sprintf;

class CalendarItemConfigStrategyIdentifierCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string,mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        /** @var CalendarItemConfigStrategy $model */
        Assert::isInstanceOf($model, CalendarItemConfigStrategy::class);

        if ($value instanceof PointCalendarStrategyType || $value instanceof PeriodCalendarStrategyType) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $calendarItem = $model->getLoadedCalendarItem();
        if (is_null($calendarItem)) {
            return $this->tryCastingValueToEnum($value) ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $value));
        }

        return match ($calendarItem->calendar_item_enum) {
            CalendarItemEnum::point() => PointCalendarStrategyType::from($value),
            CalendarItemEnum::period() => PeriodCalendarStrategyType::from($value),

            default => throw new InvalidArgumentException(sprintf('Invalid value "%s"', $value)),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string,mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        Assert::isInstanceOf($model, CalendarItemConfigStrategy::class);

        if ($value instanceof PointCalendarStrategyType || $value instanceof PeriodCalendarStrategyType) {
            return $value->value;
        }

        return $value;
    }

    private function tryCastingValueToEnum(string $value): null|PointCalendarStrategyType|PeriodCalendarStrategyType
    {
        return PointCalendarStrategyType::tryFrom($value) ?? PeriodCalendarStrategyType::tryFrom($value);
    }
}
