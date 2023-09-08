<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\CalendarItem;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Http\Requests\Api\ApiRequest;
use App\Schema\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use MinVWS\DBCO\Enum\Models\CalendarItem;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

use function is_null;

final class CreateCalendarItemRequest extends ApiRequest
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
        return [
            'label' => ['required', 'string', 'min:2'],
            'personType' => ['required', 'string', Rule::in(PolicyPersonType::allValues())],
            'itemType' => ['required', 'string', Rule::in(CalendarItem::allValues())],
            'color' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            static function (Validator $validator): void {
                if ($validator->errors()->hasAny(['itemType', 'color'])) {
                    return;
                }

                $itemType = $validator->getData()['itemType'];
                $color = $validator->getData()['color'];

                Assert::string($itemType);
                Assert::string($color);

                $itemType = CalendarItem::from($itemType);

                if (is_null(CalendarPointColor::tryFrom($color) ?? CalendarPeriodColor::tryFrom($color))) {
                    $validator->errors()->add('color', 'Veld "Color" is ongeldig.');

                    return;
                }

                $color = match ($itemType) {
                    CalendarItem::point() => CalendarPointColor::tryFrom($color),
                    default => CalendarPeriodColor::tryFrom($color),
                };

                if ($color === null) {
                    $validator->errors()->add('color', 'Ongeldige kleur/"Calendar item" combinatie!');
                }
            },
        ];
    }

    public function getDto(): CreateCalendarItemDto
    {
        $data = $this->safe();

        Assert::string($data['label']);
        Assert::string($data['personType']);
        Assert::string($data['itemType']);
        Assert::string($data['color']);

        $calendarItem = CalendarItem::from($data['itemType']);

        return new CreateCalendarItemDto(
            label: $data['label'],
            personType: PolicyPersonType::from($data['personType']),
            itemType: $calendarItem,
            color: match ($calendarItem) {
                CalendarItem::point() => CalendarPointColor::from($data['color']),
                default => CalendarPeriodColor::from($data['color']),
            },
        );
    }
}
