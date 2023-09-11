<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PseudoBsnUpdateRequest extends FormRequest
{
    private const FIELD_PSEUDO_BSN_GUID = 'pseudoBsnGuid';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::FIELD_PSEUDO_BSN_GUID => [
                'required',
                'string',
            ],
        ];
    }

    public function getPseudoBsnGuid(): string
    {
        /** @var string $value */
        $value = $this->post(self::FIELD_PSEUDO_BSN_GUID);

        return $value;
    }
}
