<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\DateOperation;

use App\Dto\Admin\UpdateDateOperationDto;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Schema\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationRelativeDay;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use RuntimeException;
use Webmozart\Assert\Assert;

use function sprintf;
use function strval;

final class UpdateDateOperationRequest extends ApiRequest
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
        /** @var array<IndexOriginDate|ContactOriginDate> */
        $originDateTypes = match ($this->getCalendarItemFromRoute()->person_type_enum) {
            PolicyPersonType::index() => IndexOriginDate::allValues(),
            PolicyPersonType::contact() => ContactOriginDate::allValues(),
            default => throw new RuntimeException(sprintf('Invalid personType "%s"', $this->getCalendarItemFromRoute()->person_type_enum)),
        };

        return [
            'relativeDay' => ['required', Rule::in(DateOperationRelativeDay::allValues())],
            'originDateType' => ['required', Rule::in($originDateTypes)],
        ];
    }

    public function getDto(): UpdateDateOperationDto
    {
        $data = $this->safe();

        Assert::integer($data['relativeDay']);
        Assert::string($data['originDateType']);

        return new UpdateDateOperationDto(
            relativeDay: DateOperationRelativeDay::from(strval($data['relativeDay'])),
            originDateType: match ($this->getCalendarItemFromRoute()->person_type_enum) {
                PolicyPersonType::index() => IndexOriginDate::from($data['originDateType']),
                PolicyPersonType::contact() => ContactOriginDate::from($data['originDateType']),
                default => IndexOriginDate::tryFrom($data['originDateType'])
                    ?? ContactOriginDate::tryFrom($data['originDateType'])
                    ?? throw new InvalidArgumentException(sprintf('Invalid value "%s"', $data['originDateType'])),
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
