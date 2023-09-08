<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\PlannerCase;

use Illuminate\Foundation\Http\FormRequest;

class PlannerSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'identifier' => 'required|min:6|max:16|string',
        ];
    }

    public function getIdentifier(): string
    {
        /** @var string $identifier */
        $identifier = $this->get('identifier');
        return $identifier;
    }
}
