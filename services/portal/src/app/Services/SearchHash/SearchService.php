<?php

declare(strict_types=1);

namespace App\Services\SearchHash;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Repositories\SearchHashCaseRepository;
use App\Repositories\SearchHashTaskRepository;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\TokenResource;
use App\Services\AuthenticationService;
use App\Services\SearchHash\Dto\SearchHashResult;
use App\Services\SearchHash\EloquentCase\Contact\ContactHash;
use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use App\Services\SearchHash\Normalizer\HashNormalizer;
use App\Services\SearchHash\Slot\Slots;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\Permission;
use MinVWS\DBCO\Enum\Models\SearchHashResultType;

use function assert;
use function in_array;
use function is_string;

final class SearchService
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly SearchHasherFactory $searchHasherFactory,
        private readonly AssignmentTokenService $assignmentTokenService,
        private readonly SearchHashCaseRepository $searchHashCaseRepository,
        private readonly SearchHashTaskRepository $searchHashTaskRepository,
        private readonly HashNormalizer $hashNormalizer,
    ) {
    }

    /**
     * @return Collection<int,SearchResult>
     */
    public function search(Slots $slots): Collection
    {
        $indexCases = $this->covidCase($slots);
        $taskCases = $this->task($slots);

        return $indexCases->merge($taskCases);
    }

    /**
     * @return Collection<int,SearchResult>
     */
    public function covidCase(Slots $slots): Collection
    {
        [
            'indexHash' => $indexHash,
            'searchHashServices' => $searchHashServices,
        ] = $this->getCovidCaseSearchHashServices($slots);

        $searchHashResultsByKeys = $this->getSearchHashResultsByKeys($searchHashServices, $slots->getIndexSlotKeys());

        $matches = $this->getMatches(
            $searchHashResultsByKeys,
            $slots->getIndexSlotKeys(),
            fn(Collection $searchHashResultsByKeys): Collection => $this->searchHashCaseRepository->getMatchingCaseUuids(
                $searchHashResultsByKeys,
            ),
        );

        return $this
            ->searchHashCaseRepository
            ->getCasesByUuids(
                caseUuids: $matches,
                organisationUuid: $this->authenticationService->getRequiredSelectedOrganisation()->uuid,
                relations: ['index'],
            )
            ->filter(function (EloquentCase $case) use ($indexHash): bool {
                // If the Index is unidentified the Index can only be "found" if it's last update was within 6 months:
                if ($case->pseudoBsnGuid === null) {
                    return CarbonImmutable::now()->subMonths(6)->lessThan($case->updatedAt);
                }

                // Index is identifed and it must only be found using the last three digits of the BSN:
                if ($indexHash->lastThreeBsnDigits === null) {
                    return false;
                }

                // Index is identified and the last three digits was given so we need to check if they match:
                assert(is_string($case->index->bsnCensored));
                return
                    $this->hashNormalizer->normalizeString($case->index->bsnCensored)
                    === $this->hashNormalizer->normalizeString($indexHash->lastThreeBsnDigits);
            })
            ->map(fn (EloquentCase $case): SearchResult
                => new SearchResult($case, $this->createToken($case->uuid), SearchHashResultType::index(), $searchHashResultsByKeys));
    }

    private function task(Slots $slots): Collection
    {
        [
            'personalDetailsHash' => $personalDetailsHash,
            'searchHashServices' => $searchHashServices,
        ] = $this->getTaskSearchHashServices($slots);

        $searchHashResultsByKeys = $this->getSearchHashResultsByKeys($searchHashServices, $slots->getIndexSlotKeys());

        $matches = $this->getMatches(
            $searchHashResultsByKeys,
            $slots->getIndexSlotKeys(),
            fn(Collection $searchHashResultsByKeys): Collection => $this->searchHashTaskRepository->getMatchingTaskUuids(
                $searchHashResultsByKeys,
            ),
        );

        return $this
            ->searchHashTaskRepository
            ->getTasksByUuids(
                taskUuids: $matches,
                organisationUuid: $this->authenticationService->getRequiredSelectedOrganisation()->uuid,
            )
            ->filter(function (EloquentTask $task) use ($personalDetailsHash): bool {
                assert($task->relationLoaded('covidCase') && $task->covidCase?->uuid !== null);

                // Contact is not identified so finding it based on last three digits is not required
                if ($task->pseudoBsnGuid === null) {
                    return true;
                }

                // Contact is identifed and it must only be found using the last three digits of the BSN:
                if ($personalDetailsHash->lastThreeBsnDigits === null) {
                    return false;
                }

                assert(is_string($task->personal_details->bsnCensored));
                return
                    $this->hashNormalizer->normalizeString($task->personal_details->bsnCensored)
                    === $this->hashNormalizer->normalizeString($personalDetailsHash->lastThreeBsnDigits);
            })
            ->map(fn (EloquentTask $task): SearchResult
                => new SearchResult(
                    $task,
                    $this->createToken($task->covidCase->uuid),
                    SearchHashResultType::contact(),
                    $searchHashResultsByKeys,
                ));
    }

    /**
     * @return array{
     *      indexHash: IndexHash,
     *      contactHash: ContactHash,
     *      searchHashServices: Collection<int,SearchHasher>
     * }
     */
    private function getCovidCaseSearchHashServices(Slots $slots): array
    {
        $indexHash = new IndexHash(
            dateOfBirth: $slots->dateOfBirth,
            lastname: $slots->lastname,
            lastThreeBsnDigits: $slots->lastThreeBsnDigits,
            postalCode: $slots->postalCode,
            houseNumber: $slots->houseNumber,
            houseNumberSuffix: $slots->houseNumberSuffix,
        );
        $contactHash = new ContactHash(dateOfBirth: $slots->dateOfBirth, phone: $slots->phone);

        /** @var Collection<int,SearchHasher> $searchHashServices */
        $searchHashServices = Collection::make([
            $this->searchHasherFactory->covidCaseIndex($indexHash),
            $this->searchHasherFactory->covidCaseContact($contactHash),
        ]);

        return [
            'indexHash' => $indexHash,
            'contactHash' => $contactHash,
            'searchHashServices' => $searchHashServices,
        ];
    }

    /**
     * @return array{
     *      personalDetailsHash: PersonalDetailsHash,
     *      generalHash: GeneralHash,
     *      searchHashServices: Collection<int,SearchHasher>
     * }
     */
    private function getTaskSearchHashServices(Slots $slots): array
    {
        $personalDetailsHash = new PersonalDetailsHash(
            dateOfBirth: $slots->dateOfBirth,
            lastThreeBsnDigits: $slots->lastThreeBsnDigits,
            postalCode: $slots->postalCode,
            houseNumber: $slots->houseNumber,
            houseNumberSuffix: $slots->houseNumberSuffix,
        );
        $generalHash = new GeneralHash(dateOfBirth: $slots->dateOfBirth, lastname: $slots->lastname, phone: $slots->phone);

        /** @var Collection<int,SearchHasher> $searchHashServcies */
        $searchHashServcies = Collection::make([
            $this->searchHasherFactory->taskPersonalDetails($personalDetailsHash),
            $this->searchHasherFactory->taskGeneral($generalHash),
        ]);

        return [
            'personalDetailsHash' => $personalDetailsHash,
            'generalHash' => $generalHash,
            'searchHashServices' => $searchHashServcies,
        ];
    }

    /**
     * @param Collection<int,SearchHasher> $searchHashers
     * @param Collection<int,non-empty-array<int,string>> $slotKeyGroups
     *
     * @return Collection<array-key,SearchHashResult>
     */
    private function getSearchHashResultsByKeys(Collection $searchHashers, Collection $slotKeyGroups): Collection
    {
        $slotKeys = $slotKeyGroups->flatten();

        return $searchHashers
            ->map(static fn (SearchHasher $hasher) => $hasher->getAllData($slotKeys)->collect())
            ->flatten(1);
    }

    /**
     * @param Collection<array-key,SearchHashResult> $searchHashResultsByKeys
     * @param Collection<array-key,non-empty-array<int,string>> $slotKeyGroups
     * @param Closure(Collection<array-key,SearchHashResult>):Collection<array-key,Collection<int,string>> $getMatchingUuidsClosure
     *
     * @return array<int,string>
     */
    private function getMatches(
        Collection $searchHashResultsByKeys,
        Collection $slotKeyGroups,
        Closure $getMatchingUuidsClosure,
    ): array {
        return $getMatchingUuidsClosure($searchHashResultsByKeys)
            ->filter(static fn (Collection $hashes)
                => $slotKeyGroups->every(static fn (array $group): bool
                    => $hashes->some(static fn (string $key): bool => in_array($key, $group, true))))
            ->keys()
            ->toArray();
    }

    /**
     * @throws AuthenticationException
     */
    private function createToken(string $caseUuid): string
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $tokenResources = Collection::make();

        if ($user->can(Permission::caseEditViaSearchCase()->value)) {
            $tokenResources->push(new TokenResource(mod: AssignmentModelEnum::Case_, ids: [$caseUuid]));
        } else {
            if ($user->can(Permission::caseCreateNote()->value)) {
                $tokenResources->push(new TokenResource(mod: AssignmentModelEnum::Note, ids: [$caseUuid]));
            }

            if ($user->can(Permission::createCallToActionViaSearchCase()->value)) {
                $tokenResources->push(new TokenResource(mod: AssignmentModelEnum::CallToAction, ids: [$caseUuid]));
            }
        }

        return $this->assignmentTokenService->createToken(tokenResources: $tokenResources, user: $user);
    }
}
