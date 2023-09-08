<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\CalendarView;

use App\Dto\Admin\UpdateCalendarViewDto;
use App\Http\Requests\Api\ApiRequest;
use App\Schema\Validation\ValidationRule;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Webmozart\Assert\Assert;

use function array_map;

final class UpdateCalendarViewRequest extends ApiRequest
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
            'label' => ['filled', 'string', 'min:2'],
            'calendarItems.*' => ['nullable', 'array'],
            'calendarItems.*.uuid' => ['string', 'exists:calendar_item,uuid'],
        ];
    }

    public function getDto(): UpdateCalendarViewDto
    {
        return new UpdateCalendarViewDto(
            label: $this->safeStringOption('label'),
            calendarItems: $this->safeCalendarItemsOption('calendarItems'),
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
    * @return Option<array<string>|array{}>
    */
    private function safeCalendarItemsOption(string $key): Option
    {
        if ($this->missing($key)) {
            return None::create();
        }

        if ($this->input($key) === null) {
            /** @phpstan-ignore-next-line */
            return Some::create([]);
        }

        $data = $this->input($key);

        Assert::isArray($data);
        return Some::create(array_map(static fn(array $value): string => $value['uuid'], $data));
    }
}
