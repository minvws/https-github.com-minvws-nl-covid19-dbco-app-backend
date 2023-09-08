<?php

declare(strict_types=1);

namespace App\Models\Task;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Eloquent\EloquentCase;
use App\Services\CalendarDataCalculationService;
use DateTimeImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use Webmozart\Assert\Assert;

use function app;
use function min;
use function sprintf;

final class DateOfLastExposureRule
{
    public function __construct(
        private readonly TaskGroup $taskGroup,
        private readonly EloquentCase $owner,
        private readonly string $dateFormat = 'Y-m-d',
    ) {
    }

    /**
     * @return array<string>
     */
    public function create(): array
    {
        try {
            $calendarDataCalculationService = app(CalendarDataCalculationService::class);
            $policyGuidelineHandler = $calendarDataCalculationService->getPolicyGuidelineHandler($this->owner);
            $facts = $calendarDataCalculationService->getPolicyFacts($this->owner);

            $period = $this->taskGroup === TaskGroup::contact()
                ? $policyGuidelineHandler->calculateContagiousPeriod($facts)
                : $policyGuidelineHandler->calculateSourcePeriod($facts);
        } catch (RiskProfileMatchNotFoundException | PolicyFactMissingException) {
            return ['prohibited'];
        }

        $endDate = min(new DateTimeImmutable(), $period->getEndDate());
        Assert::isInstanceOf($endDate, DateTimeInterface::class);

        return [
            'nullable',
            sprintf('date_format:%s', $this->dateFormat),
            sprintf('after_or_equal:%s', $period->getStartDate()->format($this->dateFormat)),
            sprintf('before_or_equal:%s', $endDate->format($this->dateFormat)),
        ];
    }
}
