<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SearchableHash;
use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\TaskRepository;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;

class TaskSearchService
{
    private TaskRepository $taskRepository;
    private SearchableHash $searchableHash;

    public function __construct(
        TaskRepository $taskRepository,
        SearchableHash $searchableHash,
    ) {
        $this->taskRepository = $taskRepository;
        $this->searchableHash = $searchableHash;
    }

    public function searchByRequest(ApiRequest $request, EloquentOrganisation $organisation): Collection
    {
        $conditions = [];

        $lastname = $request->getStringOrNull('lastname');
        $taskUuid = $request->getStringOrNull('taskUuid');
        $dateOfBirth = $request->getStringOrNull('dateOfBirth');
        $email = $request->getStringOrNull('email');
        $phone = $request->getStringOrNull('phone');

        if (!empty($taskUuid)) {
            $conditions['uuid'] = $taskUuid;
        }

        if (!empty($lastname) && !empty($dateOfBirth)) {
            try {
                $conditions['search_date_of_birth'] = $this->searchableHash->hashForLastNameAndDateOfBirth(
                    $lastname,
                    CarbonImmutable::parse($dateOfBirth),
                );
            } catch (InvalidFormatException) {
                // Invalid date, so we can't search with this criteria it
            }
        }

        if (!empty($lastname) && !empty($email)) {
            $conditions['search_email'] = $this->searchableHash->hashForLastNameAndEmail($lastname, $email);
        }

        if (!empty($lastname) && !empty($phone)) {
            $conditions['search_phone'] = $this->searchableHash->hashForLastNameAndPhone($lastname, $phone);
        }

        return $this->taskRepository->searchTasksForOrganisation($conditions, $organisation->uuid);
    }
}
