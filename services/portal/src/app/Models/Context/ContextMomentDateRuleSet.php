<?php

declare(strict_types=1);

namespace App\Models\Context;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Eloquent\EloquentCase;
use App\Services\CalendarDataCalculationService;
use Carbon\CarbonPeriod;
use DateTimeImmutable;

use function app;
use function min;
use function sprintf;

final class ContextMomentDateRuleSet
{
    private readonly CalendarDataCalculationService $calendarDataCalculationService;

    public function __construct(
        private readonly EloquentCase $owner,
    ) {
        $this->calendarDataCalculationService = app(CalendarDataCalculationService::class);
    }

    /**
     * @return array<string>
     */
    public function create(): array
    {
        try {
            $policyGuidelineHandler = $this->calendarDataCalculationService->getPolicyGuidelineHandler($this->owner);
            $facts = $this->calendarDataCalculationService->getPolicyFacts($this->owner);

            $sourcePeriod = $policyGuidelineHandler->calculateSourcePeriod($facts);
            $contagiousPeriod = $policyGuidelineHandler->calculateContagiousPeriod($facts);

            $period = CarbonPeriod::create($sourcePeriod->getStartDate(), $contagiousPeriod->getEndDate());
        } catch (RiskProfileMatchNotFoundException | PolicyFactMissingException) {
            $period = $this->calendarDataCalculationService->episodePeriod($this->owner->episode_start_date);
        }

        $dateFormat = 'Y-m-d';

        return [
            'required',
            sprintf('date_format:%s', $dateFormat),
            sprintf('after_or_equal:%s', $period->getStartDate()->format($dateFormat)),
            sprintf('before_or_equal:%s', min(new DateTimeImmutable(), $period->getEndDate())?->format($dateFormat)),
        ];
    }
}
