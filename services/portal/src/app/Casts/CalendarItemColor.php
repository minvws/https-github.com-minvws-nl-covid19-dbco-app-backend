<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Policy\CalendarItem;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use Webmozart\Assert\Assert;

use function is_string;
use function sprintf;

class CalendarItemColor implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string,mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        Assert::isInstanceOf($model, CalendarItem::class);

        if ($value instanceof CalendarPointColor || $value instanceof CalendarPeriodColor) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        return match ($model->calendar_item_enum) {
            CalendarItemEnum::point() => CalendarPointColor::from($value),
            CalendarItemEnum::period() => CalendarPeriodColor::from($value),

            default => $this->getColorOrThrowException($value),
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string,mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        Assert::isInstanceOf($model, CalendarItem::class);

        if ($value instanceof CalendarPointColor || $value instanceof CalendarPeriodColor) {
            return $value->value;
        }

        return $value;
    }

    private function getColorOrThrowException(string $value): CalendarPointColor|CalendarPeriodColor
    {
        return CalendarPointColor::tryFrom($value)
            ?? CalendarPeriodColor::tryFrom($value)
            ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $value));
    }
}
