<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\PlannerCase;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\CaseList;
use App\Models\PlannerCase\PlannerSort;
use App\Models\PlannerCase\PlannerView;
use App\Services\AuthenticationService;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\TestResultSource;

use function implode;

/**
 * @property-read CaseList|null $caseList
 * @property-read string $view
 */
class ListRequest extends ApiRequest
{
    public int $perPage;
    public int $page;
    public ?string $sort = null;
    public ?string $order = null;
    public bool $includeTotal = false;

    public function validationData(): array
    {
        $data = parent::validationData();
        $data['view'] = $this->view ?? null;
        $data['caseListUuid'] = $this->caseList->uuid ?? null;
        return $data;
    }

    protected function passedValidation(): void
    {
        $this->includeTotal = $this->query('includeTotal') === '1';
    }

    public function authorize(AuthenticationService $authenticationService): bool
    {
        if ($this->caseList === null) {
            return true;
        }

        $selectedOrganisation = $authenticationService->getRequiredSelectedOrganisation();
        return $this->caseList->organisation_uuid === $selectedOrganisation->uuid && !$this->caseList->is_queue;
    }

    public function rules(): array
    {
        $allowedSort = [
            PlannerSort::dateOfTest()->value,
            PlannerSort::updatedAt()->value,
            PlannerSort::createdAt()->value,
            PlannerSort::caseStatus()->value,
            PlannerSort::priority()->value,
        ];
        if ($this->view !== PlannerView::completed()->value) {
            $allowedSort[] = PlannerSort::contactsCount()->value;
        }

        return [
            'perPage' => 'int|min:0|max:100',
            'page' => 'int|min:1',
            'view' => 'required|string|in:' . implode(',', PlannerView::allValues()),
            'caseListUuid' => 'string|nullable|exists:case_list,uuid',
            'sort' => 'string|nullable|in:' . implode(',', $allowedSort),
            'order' => 'string|nullable|in:asc,desc',
            'includeTotal' => 'int|in:0,1',
            'organisation' => 'string|nullable|exists:organisation,uuid',
            'label' => 'string|nullable|exists:case_label,uuid',
            'userAssignment' => 'string|nullable|exists:user_organisation,user_uuid',
            'statusIndexContactTracing' => 'string|nullable|in:' . implode(',', ContactTracingStatus::allValues()),
            'testResultSource' => 'string|nullable|in:' . implode(',', TestResultSource::allValues()),
            'minAge' => 'int|nullable|min:0|max:150',
            'maxAge' => 'int|nullable|min:0|max:150',
        ];
    }
}
