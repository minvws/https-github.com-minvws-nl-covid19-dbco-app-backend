<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Foundation\Http\FormRequest;

use function sprintf;

class PseudoBsnLookupRequest extends FormRequest
{
    private const FIELD_DATE_OF_BIRTH = 'dateOfBirth';
    private const FIELD_DATE_OF_BIRTH_FORMAT = 'Y-m-d';
    private const FIELD_HOUSE_NUMBER = 'houseNumber';
    private const FIELD_HOUSE_NUMBER_SUFFIX = 'houseNumberSuffix';
    private const FIELD_POSTAL_CODE = 'postalCode';
    private const FIELD_LAST_THREE_DIGITS = 'lastThreeDigits';
    private const FIELD_BSN = 'bsn';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::FIELD_DATE_OF_BIRTH => [
                'required',
                sprintf('date_format:%s', self::FIELD_DATE_OF_BIRTH_FORMAT),
            ],
            self::FIELD_HOUSE_NUMBER => [
                'required',
                'string',
            ],
            self::FIELD_HOUSE_NUMBER_SUFFIX => [
                'nullable',
                'string',
            ],
            self::FIELD_POSTAL_CODE => [
                'required',
                'string',
                'postal_code:NL',
            ],
            self::FIELD_LAST_THREE_DIGITS => [
                'nullable',
                'string',
                'digits:3',
            ],
            self::FIELD_BSN => [
                sprintf('required_without:%s', self::FIELD_LAST_THREE_DIGITS),
                sprintf('prohibits:%s', self::FIELD_LAST_THREE_DIGITS),
                'nullable',
                'string',
                'digits_between:8,9',
            ],
        ];
    }

    public function getPostDateOfBirth(): CarbonImmutable
    {
        /** @var string $value */
        $value = $this->post(self::FIELD_DATE_OF_BIRTH);

        $date = CarbonImmutable::createFromFormat(self::FIELD_DATE_OF_BIRTH_FORMAT, $value);

        if ($date === false) {
            throw new InvalidFormatException('string could not be converted to date');
        }

        return $date->floorDay();
    }

    public function getPostHouseNumber(): string
    {
        /** @var string|null $value */
        $value = $this->post(self::FIELD_HOUSE_NUMBER);

        return (string) $value;
    }

    public function getPostHouseNumberSuffix(): ?string
    {
        /** @var string|null $value */
        $value = $this->post(self::FIELD_HOUSE_NUMBER_SUFFIX);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    public function getPostPostalCode(): string
    {
        /** @var string|null $value */
        $value = $this->post(self::FIELD_POSTAL_CODE);

        return (string) $value;
    }

    public function getLastThreeDigits(): ?string
    {
        /** @var string|null $value */
        $value = $this->post(self::FIELD_LAST_THREE_DIGITS);

        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    public function getBsn(): ?string
    {
        /** @var string|null $value */
        $value = $this->post(self::FIELD_BSN);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
