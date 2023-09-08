<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Policy\DateOperation;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

use function is_null;
use function is_string;
use function sprintf;

class OriginDateCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string,mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        /** @var DateOperation $model */
        Assert::isInstanceOf($model, DateOperation::class);

        if ($value instanceof IndexOriginDate || $value instanceof ContactOriginDate) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $calendarItem = $model->getLoadedCalendarItem();
        if (is_null($calendarItem)) {
            return $this->tryCastingValueToEnum($value) ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $value));
        }

        return match ($calendarItem->person_type_enum) {
            PolicyPersonType::index() => IndexOriginDate::from($value),
            PolicyPersonType::contact() => ContactOriginDate::from($value),

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
        Assert::isInstanceOf($model, DateOperation::class);

        if ($value instanceof IndexOriginDate || $value instanceof ContactOriginDate) {
            return $value->value;
        }

        return $value;
    }

    private function tryCastingValueToEnum(string $value): null|IndexOriginDate|ContactOriginDate
    {
        return IndexOriginDate::tryFrom($value) ?? ContactOriginDate::tryFrom($value);
    }
}
