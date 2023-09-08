<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\PlannerCase;

use App\Models\Eloquent\CaseLabel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\Priority;

class UpdateMetaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'priority' => ['sometimes', Rule::in(Priority::allValues())],
            'caseLabels' => ['sometimes', 'array'],
            'caseLabels.*' => [Rule::exists(CaseLabel::class, 'uuid')],
        ];
    }
}
