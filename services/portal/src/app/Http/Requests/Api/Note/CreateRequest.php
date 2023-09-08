<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Note;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\CaseNoteType;

class CreateRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => [
                'required',
                'filled',
                'max:5000',
            ],
            'type' => [
                'required',
                Rule::in(CaseNoteType::allValues()),
            ],
        ];
    }

    public function getNote(): string
    {
        return $this->getString('note');
    }

    public function getType(): string
    {
        return $this->getString('type');
    }
}
