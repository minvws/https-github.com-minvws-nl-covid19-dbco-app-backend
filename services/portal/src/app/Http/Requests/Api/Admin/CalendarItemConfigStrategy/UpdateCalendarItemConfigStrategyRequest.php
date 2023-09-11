<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\CalendarItemConfigStrategy;

use App\Dto\Admin\UpdateCalendarItemConfigStrategyDto;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Schema\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use RuntimeException;
use Webmozart\Assert\Assert;

use function sprintf;

final class UpdateCalendarItemConfigStrategyRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,(ValidationRule|array|string)>
     */
    public function rules(): array
    {
        /** @var array<PointCalendarStrategyType|PeriodCalendarStrategyType> */
        $stategyTypes = match ($this->getCalendarItemFromRoute()->calendar_item_enum) {
            CalendarItemEnum::point() => PointCalendarStrategyType::allValues(),
            CalendarItemEnum::period() => PeriodCalendarStrategyType::allValues(),
            default => throw new RuntimeException(
                sprintf('Invalid calendarItem "%s"', $this->getCalendarItemFromRoute()->calendar_item_enum),
            ),
        };

        return [
            'strategyType' => ['required', 'string', Rule::in($stategyTypes)],
        ];
    }

    public function getDto(): UpdateCalendarItemConfigStrategyDto
    {
        $data = $this->safe();
        Assert::string($data['strategyType']);

        return new UpdateCalendarItemConfigStrategyDto(
            strategyType: match ($this->getCalendarItemFromRoute()->calendar_item_enum) {
                CalendarItemEnum::point() => PointCalendarStrategyType::from($data['strategyType']),
                CalendarItemEnum::period() => PeriodCalendarStrategyType::from($data['strategyType']),
                default => PointCalendarStrategyType::tryFrom($data['strategyType'])
                    ?? PeriodCalendarStrategyType::tryFrom($data['strategyType'])
                    ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $data['strategyType'])),
            },
        );
    }

    private function getCalendarItemFromRoute(): CalendarItem
    {
        /** @var CalendarItemConfig */
        $calendarItemConfig = $this->route('calendar_item_config');
        return $calendarItemConfig->calendarItem;
    }
}
