<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Case\TestResult;

use App\Http\Requests\Api\ApiRequest;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;

use function implode;

/**
 * @property string $typeOfTest
 * @property ?string $customTypeOfTest
 * @property string $testResult
 * @property string $dateOfTest
 * @property string $monsterNumber
 */
class CreateManualTestResultRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'typeOfTest' => 'required|string|in:' . implode(',', TestResultTypeOfTest::allValues()),
            'customTypeOfTest' => 'required_if:typeOfTest,' . TestResultTypeOfTest::custom()->value . '|string|exclude_unless:typeOfTest,' . TestResultTypeOfTest::custom()->value,
            'result' => 'required|string|in:' . implode(',', TestResultResult::allValues()),
            'dateOfTest' => 'required|date',
            'monsterNumber' => 'string|regex:/^\d{3}[a-zA-Z]\d{1,12}$/',
            'laboratory' => 'string|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'customTypeOfTest.required_if' => 'Veld is veplicht',
        ];
    }
}
