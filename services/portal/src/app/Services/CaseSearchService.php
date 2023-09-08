<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SearchableHash;
use App\Http\Requests\Api\ApiRequest;
use App\Repositories\CaseRepository;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;

use function collect;

class CaseSearchService
{
    private CaseRepository $caseRepository;
    private SearchableHash $searchableHash;

    public function __construct(
        CaseRepository $caseRepository,
        SearchableHash $searchableHash,
    ) {
        $this->caseRepository = $caseRepository;
        $this->searchableHash = $searchableHash;
    }

    public function searchByRequest(ApiRequest $request): Collection
    {
        $conditions = [];

        $lastname = $request->getStringOrNull('lastname');
        $caseUuid = $request->getStringOrNull('caseUuid');
        $hpZoneNumber = $request->getStringOrNull('hpzoneNumber');
        $dateOfBirth = $request->getStringOrNull('dateOfBirth');
        $email = $request->getStringOrNull('email');
        $phone = $request->getStringOrNull('phone');

        if (!empty($caseUuid)) {
            $conditions['uuid'] = $caseUuid;
        }

        if (!empty($hpZoneNumber)) {
            $conditions['case_id'] = $hpZoneNumber;
        }

        if (!empty($dateOfBirth) && !empty($lastname)) {
            try {
                $conditions['search_date_of_birth'] = $this->searchableHash->hashForLastNameAndDateOfBirth(
                    $lastname,
                    CarbonImmutable::parse($dateOfBirth),
                );
            } catch (InvalidFormatException) {
                // Invalid date, so we can't search with this criteria it
            }
        }

        if (!empty($email) && !empty($lastname)) {
            $conditions['search_email'] = $this->searchableHash->hashForLastNameAndEmail($lastname, $email);
        }

        if (!empty($phone) && !empty($lastname)) {
            $conditions['search_phone'] = $this->searchableHash->hashForLastNameAndPhone($lastname, $phone);
        }

        if (empty($conditions)) {
            return collect();
        }

        return $this->caseRepository->searchCases($conditions);
    }
}
