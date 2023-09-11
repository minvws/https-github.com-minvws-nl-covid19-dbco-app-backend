<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Calendar\CalendarDataPeriodDto;
use App\Dto\Calendar\CalendarDataPointDto;
use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemRepository;
use App\Repositories\Policy\CalendarViewRepository;
use App\Repositories\Policy\PopulatorReferenceEnum;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\IndexPolicyGuidelineProvider;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\FixedCalendarPeriod;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

use function is_null;

class CalendarDataCalculationService
{
    public function __construct(
        private readonly IndexPolicyGuidelineProvider $policyGuidelineProvider,
        private readonly PolicyVersionService $policyVersionService,
        private readonly CalendarViewRepository $calendarViewRepository,
        private readonly CalendarItemRepository $calendarItemRepository,
    ) {
    }

    public function episodePeriod(CarbonImmutable $episodeStartDate): CarbonPeriod
    {
        return CarbonPeriod::create(
            $this->computeEpisodeStartDate($episodeStartDate),
            $this->computeEpisodeEndDate($episodeStartDate),
        );
    }

    private function computeEpisodeStartDate(CarbonImmutable $episodeStartDate): CarbonImmutable
    {
        return $episodeStartDate->modify('-2 weeks')->startOfWeek();
    }

    private function computeEpisodeEndDate(CarbonImmutable $episodeStartDate): CarbonImmutable
    {
        $maxDate = $episodeStartDate->modify('+2 weeks')->endOfWeek()->startOfDay();

        return $maxDate->isAfter(CarbonImmutable::today()) ? CarbonImmutable::today() : $maxDate;
    }

    public function getPolicyFacts(EloquentBaseModel $owner): IndexPolicyFacts
    {
        $facts = IndexPolicyFacts::create(
            $owner->symptoms->hasSymptoms ?? null,
            $owner->medication->isImmunoCompromised ?? null,
            $owner->hospital->isAdmitted ?? null,
            $owner->hospital->reason ?? null,
        );

        if ($owner->test->dateOfSymptomOnset) {
            $facts = $facts->withDateOfSymptomOnset(Carbon::parse($owner->test->dateOfSymptomOnset));
        }

        if ($owner->test->dateOfTest) {
            $facts = $facts->withDateOfTest(Carbon::parse($owner->test->dateOfTest));
        }

        return $facts;
    }

    public function calculatePeriods(EloquentBaseModel $owner): Collection
    {
        $periods = Collection::make();

        try {
            Assert::isInstanceOf($owner, EloquentCase::class);
            $policyVersion = $this->policyVersionService->getPolicyVersionForCase($owner);

            if (is_null($policyVersion)) {
                return $this->returnEpisodePeriod($this->episodePeriod($owner->episodeStartDate));
            }

            $calendarItemPeriods = $this->calendarItemRepository->getCalendarItems(
                $policyVersion->uuid,
                null,
                CalendarItemEnum::period(),
            );

            foreach ($calendarItemPeriods as $calendarItemPeriod) {
                /** @var CalendarItem $calendarItemPeriod */
                if (is_null($calendarItemPeriod->populator_reference_enum)) {
                    continue;
                }

                $period = $this->getCalendarItemPeriodStartAndEndDate(
                    $owner,
                    $policyVersion,
                    $calendarItemPeriod->populator_reference_enum,
                );
                $periods->push(new CalendarDataPeriodDto(
                    id: $calendarItemPeriod->uuid,
                    startDate: $period['startDate'],
                    endDate: $period['endDate'],
                    key: $period['key'],
                    label: $calendarItemPeriod->label,
                    color: $calendarItemPeriod->color_enum->value,
                ));
            }

            return $periods;
        } catch (RiskProfileMatchNotFoundException | PolicyFactMissingException) {
            // If no source and contagious period can be returned, we return the episodePeriod
            return $this->returnEpisodePeriod($this->episodePeriod($owner->episodeStartDate));
        }
    }

    /**
     * @return Collection<CalendarDataPeriodDto>
     */
    private function returnEpisodePeriod(CarbonPeriod $episodePeriod): Collection
    {
        return Collection::make([
            new CalendarDataPeriodDto(
                id: 'episode',
                startDate: $episodePeriod->getStartDate(),
                endDate: $episodePeriod->getIncludedEndDate(),
                key: FixedCalendarPeriod::episode(),
            ),
        ]);
    }

    public function calculatePoints(EloquentBaseModel $owner): Collection
    {
        Assert::isInstanceOf($owner, EloquentCase::class);
        $policyVersion = $this->policyVersionService->getPolicyVersionForCase($owner);

        Assert::notNull($policyVersion);
        $calendarItemPoints = $this->calendarItemRepository->getCalendarItems(
            $policyVersion->uuid,
            PolicyPersonType::index(),
            CalendarItemEnum::point(),
        );

        $points = Collection::make();
        foreach ($calendarItemPoints as $calendarItemPoint) {
            /** @var CalendarItem $calendarItemPoint */
            if (is_null($calendarItemPoint->populator_reference_enum)) {
                continue;
            }

            $calendarItemDate = $this->getCalendarItemPointDate($owner, $calendarItemPoint->populator_reference_enum);
            if (is_null($calendarItemDate)) {
                continue;
            }

            $points->push(new CalendarDataPointDto(
                id: $calendarItemPoint->uuid,
                date: $calendarItemDate,
                label: $calendarItemPoint->label,
                color: $calendarItemPoint->color_enum->value,
            ));
        }

        return $points;
    }

    public function getViews(EloquentCase $owner): array
    {
        $policyVersion = $this->policyVersionService->getPolicyVersionForCase($owner);

        if (is_null($policyVersion)) {
            return [];
        }

        /** @var Collection<CalendarView> $calenderViews */
        $calenderViews = $this->calendarViewRepository->getCalendarViews($policyVersion->uuid);

        return $calenderViews->mapWithKeys(static function ($calendarView): array {
            Assert::isInstanceOf($calendarView, CalendarView::class);

            return [
                $calendarView->calendar_view_enum->value =>
                    $calendarView->calendarItems->map(
                        static function (CalendarItem $calendarItem): string {
                            return $calendarItem->uuid;
                        },
                    )->toArray(),
            ];
        })->toArray();
    }

    public function getPolicyGuidelineHandler(EloquentBaseModel $owner): PolicyGuidelineHandler
    {
        Assert::isInstanceOf($owner, EloquentCase::class);

        $facts = $this->getPolicyFacts($owner);
        $policyVersion = $this->policyVersionService->getPolicyVersionForCase($owner);

        return $this->policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts, $policyVersion);
    }

    private function getCalendarItemPeriodStartAndEndDate(
        EloquentBaseModel $owner,
        PolicyVersion $policyVersion,
        PopulatorReferenceEnum $populatorReferenceEnum,
    ): array {
        $facts = $this->getPolicyFacts($owner);
        $policyProfileHandler = $this->policyGuidelineProvider->getByPolicyVersionApplicableByFacts($facts, $policyVersion);

        switch ($populatorReferenceEnum) {
            case PopulatorReferenceEnum::SourcePeriod:
                $period = $policyProfileHandler->calculateSourcePeriod($facts);
                $key = FixedCalendarPeriod::source();
                break;
            case PopulatorReferenceEnum::ContagiousPeriod:
                $period = $policyProfileHandler->calculateContagiousPeriod($facts);
                $key = FixedCalendarPeriod::contagious();
                break;
            default:
                $period = $this->episodePeriod($owner->episodeStartDate);
                break;
        }

        return [
            'startDate' => $period->getStartDate(),
            'endDate' => $period->getIncludedEndDate(),
            'key' => $key ?? null,
        ];
    }

    private function getCalendarItemPointDate(
        EloquentBaseModel $owner,
        PopulatorReferenceEnum $populatorReferenceEnum,
    ): ?CarbonInterface {
        return match ($populatorReferenceEnum) {
            PopulatorReferenceEnum::DateOfSymptomOnset => $owner->test->dateOfSymptomOnset ? Carbon::parse(
                $owner->test->dateOfSymptomOnset,
            ) : null,
            PopulatorReferenceEnum::DateOfTestIndex => $owner->test->dateOfTest
                ? Carbon::parse($owner->test->dateOfTest) : null,
            default => null,
        };
    }
}
