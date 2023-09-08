<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Context\ContextMomentDateRuleSet;
use App\Models\Eloquent\EloquentCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LogicException;
use MinVWS\DBCO\Enum\Models\ContextRelationship;

abstract class ContextFormRequest extends FormRequest
{
    protected EloquentCase $case;

    public function setCase(EloquentCase $case): void
    {
        $this->case = $case;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function validateResolved(): void
    {
        if (!isset($this->case)) {
            return;
        }

        parent::validateResolved();
    }

    /**
     * @inheritDoc
     *
     * When returning the validated data from the request, make sure the CovidCase is injected and
     * perform the validation again since it most probably didn't run the first time due to the missing
     * CovidCase (see validateResolved())
     */
    public function validated($key = null, $default = null): array
    {
        if (!isset($this->case)) {
            throw new LogicException('CovidCase owner must be set to validate the request');
        }

        $this->validateResolved();

        return parent::validated();
    }

    public function rules(): array
    {
        if (!isset($this->case)) {
            throw new LogicException('The CovidCase model must be set to validate the request');
        }

        return [
            'context' => 'required',
            'context.label' => 'nullable',
            'context.explanation' => 'nullable|max:5000',
            'context.detailedExplanation' => 'nullable|max:5000',
            'context.remarks' => 'nullable|max:5000',
            'context.moments.*' => (new ContextMomentDateRuleSet($this->case))->create(),
            'context.placeUuid' => 'nullable|uuid',
            'context.relationship' => 'nullable|string|' . Rule::in(ContextRelationship::allValues()),
            'context.otherRelationship' => 'nullable|string',
            'context.uuid' => 'nullable|uuid',
            'context.isSource' => 'nullable|boolean',
        ];
    }
}
