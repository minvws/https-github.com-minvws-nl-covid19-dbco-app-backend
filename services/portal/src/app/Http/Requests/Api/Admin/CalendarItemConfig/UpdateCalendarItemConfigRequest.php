<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\CalendarItemConfig;

use App\Dto\Admin\UpdateCalendarItemConfigDto;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Policy\CalendarItemConfig;
use App\Schema\Validation\ValidationRule;
use Illuminate\Validation\Rule;

use function assert;
use function is_bool;

final class UpdateCalendarItemConfigRequest extends ApiRequest
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
        /** @var CalendarItemConfig */
        $calendarItemConfig = $this->route('calendar_item_config');

        return [
            'isHidden' => ['required', 'bool', Rule::prohibitedIf(!$calendarItemConfig->calendarItem->isHideable())],
        ];
    }

    public function getDto(): UpdateCalendarItemConfigDto
    {
        $data = $this->safe();
        assert(is_bool($data['isHidden']));
        return new UpdateCalendarItemConfigDto(isHidden: $data['isHidden']);
    }
}
