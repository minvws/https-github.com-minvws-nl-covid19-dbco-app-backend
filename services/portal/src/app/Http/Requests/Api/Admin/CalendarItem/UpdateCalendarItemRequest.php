<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\CalendarItem;

use App\Dto\Admin\UpdateCalendarItemDto;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Policy\CalendarItem;
use App\Schema\Validation\ValidationRule;
use Illuminate\Validation\Validator;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Webmozart\Assert\Assert;

use function is_null;

final class UpdateCalendarItemRequest extends ApiRequest
{
    private ?CalendarItemEnum $calendarItemEnum = null;

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
            'label' => ['filled', 'string', 'min:2'],
            'color' => ['filled', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var CalendarItem $calendarItem */
                $calendarItem = $this->route('calendar_item');

                $calendarItemEnum = $calendarItem->calendar_item_enum;

                $this->calendarItemEnum = $calendarItemEnum;

                if ($validator->errors()->has('color') || !isset($validator->getData()['color'])) {
                    return;
                }

                $color = $validator->getData()['color'];

                Assert::string($color);

                if (is_null(CalendarPointColor::tryFrom($color) ?? CalendarPeriodColor::tryFrom($color))) {
                    $validator->errors()->add('color', 'Veld "Color" is ongeldig.');

                    return;
                }

                $color = match ($this->calendarItemEnum) {
                    CalendarItemEnum::point() => CalendarPointColor::tryFrom($color),
                    default => CalendarPeriodColor::tryFrom($color),
                };

                if ($color === null) {
                    $validator->errors()->add('color', 'Ongeldige kleur/"Calendar item" combinatie!');
                }
            },
        ];
    }

    public function getDto(): UpdateCalendarItemDto
    {
        return new UpdateCalendarItemDto(
            label: $this->safeStringOption('label'),
            color: $this->safeColorEnumOption('color'),
        );
    }

    /**
     * @return Option<string>
     */
    private function safeStringOption(string $key): Option
    {
        if ($this->safe()->missing($key)) {
            return None::create();
        }

        /** @var string $data */
        $data = $this->safe()[$key];

        Assert::string($data);

        return Some::create($data);
    }

   /**
     * @return Option<CalendarPointColor|CalendarPeriodColor>
     */
    private function safeColorEnumOption(string $key): Option
    {
        if ($this->safe()->missing($key)) {
            return None::create();
        }

        /** @var string $data */
        $data = $this->safe()[$key];

        Assert::string($data);
        Assert::isInstanceOf($this->calendarItemEnum, CalendarItemEnum::class);

        $enum = match ($this->calendarItemEnum) {
            CalendarItemEnum::point() => CalendarPointColor::from($data),
            default => CalendarPeriodColor::from($data),
        };

        return Some::create($enum);
    }
}
