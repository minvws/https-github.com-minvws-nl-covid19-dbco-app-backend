<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Task\DateOfLastExposureRule;
use App\Services\CaseService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use Webmozart\Assert\Assert;

use function app;
use function count;

/**
 * @deprecated use \App\Models\Eloquent\EloquentTask, see DBCO-3004
 */
class Task implements Validatable
{
    public const TASK_STATUS_OPEN = 'open';
    public const TASK_STATUS_CLOSED = 'closed';
    public const TASK_STATUS_DELETED = 'deleted';

    public string $uuid;

    public string $caseUuid;

    public string $internalReference;

    public string $taskType;

    public string $source;

    public ?string $label = null;

    public ?string $derivedLabel;

    public ?string $category;

    public ?string $taskContext = null;

    public ?string $nature;

    public ?CarbonInterface $dateOfLastExposure = null;
    public ?array $contactDates = null;

    public ?string $communication = null;

    public ?CarbonInterface $informedByIndexAt = null;

    public ?CarbonInterface $informedByStaffAt = null;

    public ?CarbonInterface $createdAt = null;

    public ?CarbonInterface $updatedAt = null;

    public ?CarbonInterface $deletedAt = null;

    public ?array $answers;

    // Filled upon submit, indicates which questionnaire the user filled
    public ?string $questionnaireUuid;

    public ?string $progress = null;

    public ?string $exportId = null;

    public ?CarbonInterface $exportedAt = null;
    public ?CarbonInterface $copiedAt = null;

    public string $status = self::TASK_STATUS_OPEN;

    public ?TaskGroup $group = null;

    public bool $isSource = false;

    public ?string $dossierNumber = null;

    public ?string $email = null;

    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $telephone = null;

    public InformStatus $informStatus;

    public ?string $pseudoBsnGuid = null;

    public function __construct()
    {
        /** @var InformStatus $defaultItem */
        $defaultItem = InformStatus::defaultItem();

        $this->informStatus = $defaultItem;
    }

    public function getFullName(): string
    {
        $fullname = new Collection([$this->firstname, $this->lastname]);
        return $fullname->whereNotNull()->implode(' ');
    }

    /**
     * @inheritDoc
     */
    public static function validationRules(array $data): array
    {
        $createRules = [
            'uuid' => 'nullable',
            'group' => 'required|string|' . Rule::in(TaskGroup::allValues()),
            'label' => 'required', // can be null if derivedLabel was set
            'taskContext' => 'nullable',
            'nature' => 'nullable',
            'category' => 'nullable|string|' . Rule::in(ContactCategory::allValues()),
            'communication' => 'nullable|in:staff,index',
            'isSource' => 'nullable',
        ];
        $updateRules = [
            'uuid' => 'required',
            'group' => 'nullable|string|' . Rule::in(TaskGroup::allValues()),
            'label' => 'nullable', // can be null if derivedLabel was set
            'taskContext' => 'nullable',
            'nature' => 'nullable',
            'category' => 'nullable|string|' . Rule::in(ContactCategory::allValues()),
            'communication' => 'nullable',
            'informedByStaffAt' => 'nullable',
            'isSource' => 'nullable',
            'informStatus' => 'nullable|string|' . Rule::in(InformStatus::allValues()),
        ];

        $rules = [];
        $rules[self::SEVERITY_LEVEL_FATAL] = empty($data['uuid']) ? $createRules : $updateRules;

        $taskGroup = TaskGroup::tryFromOptional($data['group']);
        $eloquentTask = app(CaseService::class)->getCaseByUuid($data['caseUuid']);
        Assert::notNull($eloquentTask);
        $dateOfLastExposureRule = $taskGroup ? (new DateOfLastExposureRule(
            $taskGroup,
            $eloquentTask,
        ))->create() : ['nullable', 'prohibited'];

        $severityLevel = isset($dateOfLastExposureRule[0]) && $dateOfLastExposureRule[0] === 'prohibited' && count(
            $dateOfLastExposureRule,
        ) === 1
            ? self::SEVERITY_LEVEL_WARNING
            : self::SEVERITY_LEVEL_FATAL;
        $rules[$severityLevel]['dateOfLastExposure'] = $dateOfLastExposureRule;

        return $rules;
    }
}
