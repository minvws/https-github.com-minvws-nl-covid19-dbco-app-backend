<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use DateTimeInterface;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function array_merge;

class Utils
{
    private const OSIRIS_DATE_FORMAT = 'd-m-Y';

    /**
     * Format date string according to the default Osiris date format.
     *
     * @return ($date is null ? null : string)
     */
    public static function formatDate(?DateTimeInterface $date): ?string
    {
        return $date?->format(self::OSIRIS_DATE_FORMAT);
    }

    /**
     * Returns the minimum date given one or more dates.
     */
    public static function minDate(?DateTimeInterface $date, ?DateTimeInterface ...$otherDates): ?DateTimeInterface
    {
        $minDate = $date;
        foreach ($otherDates as $current) {
            if (!$current instanceof DateTimeInterface) {
                continue; // skip nulls
            }

            $minDate = $minDate === null || $current < $minDate ? $current : $minDate;
        }

        return $minDate;
    }

    /**
     * Iterates through the collection, capped at the given maximum number of items, and calls the
     * callback to collect answers.
     *
     * @template T
     *
     * @param array<T>|Collection<T>|null $collection
     * @param callable(T, int): array<Answer> $callback
     *
     * @return array<Answer>
     */
    public static function collectAnswers(array|Collection|null $collection, int $max, callable $callback): array
    {
        $answers = [];
        foreach ($collection ?? [] as $i => $item) {
            if ($i >= $max) {
                break;
            }

            $answers = array_merge($answers, $callback($item, $i));
        }

        return $answers;
    }

    /**
     * @return Collection<EloquentTask>
     */
    public static function getContactsAndSources(EloquentCase $case): Collection
    {
        /** @var Collection<EloquentTask> $tasks */
        $tasks = $case->tasks;
        return $tasks;
    }

    /**
     * @return Collection<int,EloquentTask>
     */
    public static function getContacts(EloquentCase $case): Collection
    {
        return self::getContactsAndSources($case)->whereIn('taskGroup', [TaskGroup::contact()]);
    }

    /**
     * @return Collection<EloquentTask>
     */
    public static function getSources(EloquentCase $case): Collection
    {
        return self::getContactsAndSources($case)->whereIn('taskGroup', [TaskGroup::positiveSource(), TaskGroup::symptomaticSource()]);
    }

    /**
     * @return ($value is null ? null : string)
     */
    public static function mapYesNoUnknown(?YesNoUnknown $value): ?string
    {
        return match ($value) {
            YesNoUnknown::yes() => 'J',
            YesNoUnknown::no() => 'N',
            YesNoUnknown::unknown() => 'Onb',
            default => null
        };
    }
}
