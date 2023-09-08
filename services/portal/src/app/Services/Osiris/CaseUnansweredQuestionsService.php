<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Dto\Osiris\CaseUnansweredQuestionsResult;
use App\Models\CovidCase\Contracts\Validatable;
use App\Schema\Traits\ValidationTagging;
use App\Services\CaseFragmentService;
use Exception;
use Illuminate\Support\Facades\Validator;

use function count;

class CaseUnansweredQuestionsService
{
    use ValidationTagging;

    public function __construct(
        private readonly CaseFragmentService $caseFragmentService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getByCaseUuid(string $caseUuid, bool $finished): CaseUnansweredQuestionsResult
    {
        $fragments = $this->caseFragmentService::fragmentNames();
        $level = $finished ? Validatable::SEVERITY_LEVEL_OSIRIS_FINISHED : Validatable::SEVERITY_LEVEL_OSIRIS_APPROVED;

        $errors = [];
        foreach ($fragments as $fragment) {
            $keys = $this->checkForFragment($caseUuid, $fragment, $level);

            if (count($keys) > 0) {
                $errors[$fragment] = $keys;
            }
        }

        return CaseUnansweredQuestionsResult::create($errors);
    }

    /**
     * @throws Exception
     */
    private function checkForFragment(string $caseUuid, string $fragment, string $level): array
    {
        $class = CaseFragmentService::fragmentClasses()[$fragment];
        $data = $this->caseFragmentService->encodeFragment(
            'index',
            $this->caseFragmentService->loadFragment($caseUuid, $fragment),
        );

        //Needed for fragment validation rules
        $data['caseUuid'] = $caseUuid;

        $fragmentValidationRules = $class::validationRules($data);
        $fragmentRules = $this->mapRules($fragmentValidationRules);

        $rules = [];
        if (isset($fragmentRules[$level])) {
            $rules = $fragmentRules[$level];
        }

        if (empty($rules)) {
            return [];
        }

        $validator = Validator::make($data, $rules);

        return $validator->errors()->keys();
    }
}
