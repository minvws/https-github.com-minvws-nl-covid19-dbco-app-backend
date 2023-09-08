<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CovidCase;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use App\Repositories\ContextRepository;
use App\Repositories\MomentRepository;
use App\Repositories\SectionRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContextCategorySuggestionGroup;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use Ramsey\Uuid\Uuid;

use function collect;
use function in_array;
use function sprintf;

use const PHP_EOL;

class ContextService
{
    public function __construct(
        private readonly ContextRepository $contextRepository,
        private readonly MomentRepository $momentRepository,
        private readonly PlaceService $placeService,
        private readonly SectionRepository $sectionRepository,
        private readonly CaseService $caseService,
    ) {
    }

    public function getContext(string $contextUuid): ?Context
    {
        return $this->contextRepository->getContext($contextUuid);
    }

    /**
     * @param array<string> $moments
     */
    public function createContext(
        ?string $label,
        ?string $placeUuid,
        ?ContextRelationship $relationship,
        ?string $otherRelationship,
        ?string $explanation,
        ?string $detailedExplanation,
        ?string $remarks,
        ?bool $isSource,
        ?array $moments,
        EloquentCase $case,
    ): Context {
        $context = $case->createContext();
        $context->created_at ??= CarbonImmutable::now();
        $context->updated_at ??= $context->created_at;
        $context->uuid = Uuid::uuid4()->toString();
        $context->label = $label;
        $context->place_uuid = $placeUuid;
        $context->relationship = $relationship;
        $context->other_relationship = $otherRelationship;
        $context->explanation = $explanation;
        $context->detailed_explanation = $detailedExplanation;
        $context->remarks = $remarks;
        $context->is_source = $isSource;
        $context->save();

        $this->updatesMomentsForContext($context, $moments ?? []);

        // Refresh the associated Moments
        $context->refresh();

        return $context;
    }

    /**
     * @param array<string> $moments
     */
    public function updateContext(
        Context $context,
        ?string $label,
        ?string $placeUuid,
        ?ContextRelationship $relationship,
        ?string $otherRelationship,
        ?string $explanation,
        ?string $detailedExplanation,
        ?string $remarks,
        bool $isSource,
        ?array $moments,
        ?CovidCase $case = null,
    ): Context {
        if ($context->place_uuid !== $placeUuid) {
            if ($context->place_uuid !== null) {
                $this->unlinkPlaceFromContext($context, $context->place);
            }

            $context->place_uuid = $placeUuid;
        }

        $context->label = $label;
        $context->relationship = $relationship;
        $context->other_relationship = $otherRelationship;
        $context->explanation = $explanation;
        $context->detailed_explanation = $detailedExplanation;
        $context->remarks = $remarks;
        $context->is_source = $isSource;

        // Make it possible to update the Context without needing the Case
        if ($case !== null) {
            $context->covidcase_uuid = $case->uuid;
        }
        $context->save();

        $this->updatesMomentsForContext($context, $moments ?? []);

        // Refresh the associated Moments
        $context->refresh();

        return $context;
    }

    /**
     * @return Collection<int,Context>
     */
    public function getContextsForCase(
        EloquentCase $case,
        ?string $group = null,
        ?bool $withRelationships = false,
    ): Collection {
        if ($group === null && $withRelationships === true) {
            return $this->contextRepository->getContextsByCaseWithRelationships($case);
        }

        $dateRange = $this->getContextDateRangeByCase($case, $group);
        if (!$dateRange) {
            return $this->contextRepository->getContextsByCase($case);
        }

        return $this->contextRepository->getContextsByCaseAndDateRange($case, $dateRange['startDate'], $dateRange['endDate']);
    }

    public function countContextsByCaseAndGroup(EloquentCase $case, string $group): int
    {
        $dateRange = $this->getContextDateRangeByCase($case, $group);
        if (!$dateRange) {
            return 0;
        }
        return $this->contextRepository->countContextsByCaseAndDateRange($case, $dateRange['startDate'], $dateRange['endDate']);
    }

    /**
     * @return array{startDate: ?CarbonInterface, endDate: ?CarbonInterface}|null
     */
    private function getContextDateRangeByCase(EloquentCase $eloquentCase, ?string $group = null): array|null
    {
        $case = $this->caseService->getCovidCaseFromEloquentModel($eloquentCase);

        $dateRange = [];

        switch ($group) {
            case 'source':
                $dateRange['startDate'] = $case->calculateSourcePeriodStart();
                $dateRange['endDate'] = $case->calculateSourcePeriodEnd();
                break;
            case 'contagious':
                $dateRange['startDate'] = $case->calculateContagiousPeriodStart();
                $dateRange['endDate'] = CarbonImmutable::now();
                break;
            default:
                return null;
        }

        return $dateRange;
    }

    /**
     * @param array<string> $newMoments
     */
    public function updatesMomentsForContext(Context $context, array $newMoments): void
    {
        // Get a list of already persisted Moments
        $existing = $this->momentRepository->getAllMomentsByContext($context->uuid);

        // Remove existing Moments that are no longer on the list
        $unchanged = [];
        foreach ($existing as $moment) {
            $day = $moment->day->format('Y-m-d');
            if (!in_array($day, $newMoments, true)) {
                $moment->delete();
            } else {
                // Keep track to prevent creating duplicates
                $unchanged[] = $day;
            }
        }

        // Create newly submitted Moments
        foreach ($newMoments as $date) {
            if (!in_array($date, $unchanged, true)) {
                $moment = new Moment();
                $moment->context_uuid = $context->uuid;
                $moment->day = $date;
                $moment->save();
            }
        }
    }

    public function unlinkPlaceFromContext(Context $context, Place $place): void
    {
        if ($context->place->uuid !== $place->uuid) {
            throw new InvalidArgumentException('Place is not linked to this context');
        }

        // Temporary hotfix to prevent data-loss in production, @see https://egeniq.atlassian.net/browse/DBCO-4038
        $this->sectionRepository->unlinkSectionsFromContext($context, false);

        $context->place_uuid = null;
        $context->place_added_at = null;
        $context->save();

        if ($place->contexts()->count() === 0 && $place->is_verified !== true) {
            $place->delete();
        }
    }

    private function buildContextExplanation(
        Context $context,
        string $inputPlaceUuid,
        ?string $inputExplanation,
    ): ?string {
        $strippedInputExplanation = Str::remove(["\r", "\n", ' '], (string) $inputExplanation, false);

        // retrieve list of suggestions for the previous place
        $oldPlace = $this->placeService->getPlace($context->place_uuid);
        if ($oldPlace !== null) {
            $oldPlaceContextCategorySuggestionGroup = ContextCategorySuggestionGroup::tryFromOptional(
                $oldPlace->category?->suggestionGroup,
            );
            if ($oldPlaceContextCategorySuggestionGroup) {
                $oldPlaceSuggestions = collect($oldPlaceContextCategorySuggestionGroup->suggestions)
                    ->values()
                    ->implode('');

                // if input is not empty and different from old place suggestions, just return the input
                $isInputEqualToOldPlaceSuggestions = Str::of($strippedInputExplanation)->exactly($oldPlaceSuggestions);
                if ($strippedInputExplanation !== '' && !$isInputEqualToOldPlaceSuggestions) {
                    return $inputExplanation;
                }
            }
        }

        // the input is empty or equal to the old place suggestions, we should replace it with the new place suggestions
        $newPlace = $this->placeService->getPlace($inputPlaceUuid);
        if ($newPlace === null) {
            return $inputExplanation;
        }

        $newPlaceContextCategorySuggestionGroup = ContextCategorySuggestionGroup::tryFromOptional($newPlace->category?->suggestionGroup);

        if ($newPlaceContextCategorySuggestionGroup === null) {
            return $inputExplanation;
        }
        $newPlaceSuggestions = collect($newPlaceContextCategorySuggestionGroup->suggestions)->values();

        // return list of items, separated by 2 newlines
        return $newPlaceSuggestions->implode(sprintf('%s%s%s', PHP_EOL, PHP_EOL, PHP_EOL));
    }

    public function linkPlaceToContext(Context $context, Place $place): void
    {
        if (!empty($context->place_uuid)) {
            $this->unlinkPlaceFromContext($context, $context->place);
        }

        $context->place_uuid = $place->uuid;
        $context->place_added_at = CarbonImmutable::now();
        $context->explanation = $this->buildContextExplanation($context, (string) $place->uuid, $context->explanation);
        $context->save();

        if (empty($place->organisation_uuid)) {
            $this->placeService->setPlaceOrganisationFromCase($place, $context->case);
        }
    }
}
