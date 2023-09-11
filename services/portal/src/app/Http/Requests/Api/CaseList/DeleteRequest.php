<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseList;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\CaseList;

/**
 * @property CaseList $caseList
 */
class DeleteRequest extends ApiRequest
{
    public function authorize(): bool
    {
        if ($this->caseList->is_default) {
            return false;
        }

        $force = $this->query('force') === '1';
        return $force || !$this->caseList->isInUse();
    }

    public function rules(): array
    {
        return [
            'force' => ['string', 'in:0,1'],
        ];
    }
}
