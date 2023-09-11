<?php

declare(strict_types=1);

namespace App\Http\Responses\PlannerCase;

use App\Dto\Calendar\CalendarDataPointDto;
use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Section;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo6;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\AuthenticationService;
use App\Services\CalendarDataCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use MinVWS\Codable\DateTimeFormatException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function array_filter;
use function is_null;

class CovidCaseWithAssignmentTokenEncoder implements EncodableDecorator
{
    public function __construct(
        private readonly AssignmentTokenService $assignmentTokenService,
        private readonly CovidCaseEncoder $covidCaseEncoder,
        private readonly AuthenticationService $authenticationService,
        private readonly CalendarDataCalculationService $caseComputeService,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws DateTimeFormatException
     */
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentCase) {
            return;
        }

        $this->covidCaseEncoder->encode($value, $container);

        $this->encodeContextProperties($value, $container);

        $container->relationContext = $value->context_relationship ?? '-';
        $container->token = $this->assignmentTokenService->createTokenForCases(
            [$value->uuid],
            $this->authenticationService->getAuthenticatedUser(),
        );

        $container->symptoms = array_filter([
            'hasSymptoms' => $value->symptoms->hasSymptoms,
            'stillHadSymptomsAt' => $value instanceof CovidCaseV1UpTo6 ? $value->symptoms->stillHadSymptomsAt : null,
        ]);
        $container->hospital = [
            'reason' => $value->hospital->reason,
            'isAdmitted' => $value->hospital->isAdmitted,
        ];
    }

    private function encodeContextProperties(EloquentCase $value, EncodingContainer $container): void
    {
        $context = $this->getContextFromEloquentCase($value);
        $contact = $context?->contact;
        $circumstances = $context?->circumstances;

        $container->notificationNamedConsent = $contact?->notificationNamedConsent;
        $container->firstName = $contact?->notificationNamedConsent ? $value->index->firstname : null;
        $container->lastName = $contact?->notificationNamedConsent ? $value->index->lastname : null;

        $container->moments = $this->getContextMoments($value, $context)->toArray();
        $container->sections = $context?->sections?->map(static fn (Section $s): string => $s->label)->toArray();
        $container->causeForConcern = $circumstances?->causeForConcern;
        $container->isDeceased = $value->deceased->isDeceased;
    }

    private function getContextFromEloquentCase(EloquentCase $value): ?Context
    {
        $context = $value->contexts()->where('uuid', $value->context_uuid)->first();

        if ($context instanceof Context) {
            return $context;
        }

        return null;
    }

    /**
     * @return Collection<CalendarDataPointDto>
     */
    private function getContextMoments(EloquentCase $case, ?Context $context): Collection
    {
        try {
            $facts = $this->caseComputeService->getPolicyFacts($case);
            $policyGuidelineHandler = $this->caseComputeService->getPolicyGuidelineHandler($case);
            $contagiousPeriod = $policyGuidelineHandler->calculateContagiousPeriod($facts);
            $sourcePeriod = $policyGuidelineHandler->calculateSourcePeriod($facts);
        } catch (RiskProfileMatchNotFoundException | PolicyFactMissingException) {
            $contagiousPeriod = null;
            $sourcePeriod = null;
        }

        return $context?->moments->map(static function ($moment) use ($sourcePeriod, $contagiousPeriod) {
            $carbon = CarbonImmutable::parse($moment->day);
            $isSource = !is_null($sourcePeriod)
                ? $carbon->betweenIncluded(
                    $sourcePeriod->getStartDate(),
                    $sourcePeriod->getIncludedEndDate(),
                ) : false;
            $isContagious = !is_null($contagiousPeriod)
                ? $carbon->betweenIncluded(
                    $contagiousPeriod->getStartDate(),
                    $contagiousPeriod->getIncludedEndDate(),
                )
                : false;

            if ($isSource && $isContagious) {
                return new CalendarDataPointDto(
                    id: $moment->uuid,
                    date: $carbon,
                    label: 'overlap date',
                    icon: 'range-overlap',
                );
            }
            if ($isSource) {
                return new CalendarDataPointDto(
                    id: $moment->uuid,
                    date: $carbon,
                    label: 'source dates',
                    icon: 'circle-blue',
                );
            }

            if ($isContagious) {
                return new CalendarDataPointDto(
                    id: $moment->uuid,
                    date: $carbon,
                    label: 'infectious dates',
                    icon: 'square-red',
                );
            }

            return new CalendarDataPointDto(
                id: $moment->uuid,
                date: $carbon,
                label: 'unknown dates',
                icon: 'diamond-grey',
            );
        });
    }
}
