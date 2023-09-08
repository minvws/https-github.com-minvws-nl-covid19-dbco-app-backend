<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Models\Context\Moment;
use App\Models\CovidCase;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Services\CaseFragmentService;
use App\Services\CaseService;
use App\Services\ContextFragmentService;
use App\Services\ContextService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use Webmozart\Assert\Assert;

use function array_merge;
use function collect;
use function count;
use function sprintf;
use function view;

class ApiHpZoneCopyController extends ApiController
{
    public function __construct(
        private readonly CaseFragmentService $caseFragmentService,
        private readonly CaseService $caseService,
        private readonly ContextFragmentService $contextFragmentService,
        private readonly ContextService $contextService,
    ) {
    }

    /**
     * Copy diagnostics
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Kopieertabellen van diagnostics opgehaald')]
    public function diagnostics(EloquentCase $eloquentCase, AuditEvent $auditEvent): View
    {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        $auditEvent->object($caseAuditObject);

        $case = $this->caseService->getCovidCaseFromEloquentModel($eloquentCase);
        Assert::notNull($case);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        $fragments = $this->caseFragmentService->loadFragments($eloquentCase->uuid, [
            'contacts',
            'deceased',
            'eduDaycare',
            'hospital',
            'medication',
            'index',
            'job',
            'pregnancy',
            'principalContextualSettings',
            'recentBirth',
            'symptoms',
            'test',
            'underlyingSuffering',
            'communication',
            'immunity',
            'extensiveContactTracing',
        ]);

        /** @var array $data */
        $data = array_merge($fragments, [
            'case' => $case,
            'tasks' => $this->caseService->getContactTasks($eloquentCase->uuid),
            'contexts' => $this->buildContextsCollection($eloquentCase),
        ]);

        return view('copy.diagnostics', $data);
    }

    /**
     * @param array<string> $groups
     */
    private function buildContextsCollection(EloquentCase $eloquentCase, array $groups = []): Collection
    {
        $contexts = $this->contextService->getContextsForCase($eloquentCase, null, true);

        if (count($groups) > 0) {
            $contexts = $contexts->filter(static function (Context $context) use ($groups) {
                return Str::contains($context->relationship->value ?? null, $groups);
            });
        }

        $case = $this->caseService->getCovidCaseFromEloquentModel($eloquentCase);
        Assert::notNull($case);

        return $contexts->map(function (Context $context) use ($case) {
            $fragments = $this->contextFragmentService->loadFragments($context->uuid, ['contact', 'general', 'circumstances']);

            $data = [
                'address' => null,
            ];

            if ($context->place) {
                $data['address'] = sprintf(
                    '%s %s%s, %s %s',
                    $context->place->street,
                    $context->place->housenumber,
                    $context->place->housenumber_suffix,
                    $context->place->postalcode,
                    $context->place->town,
                );
            }

            return array_merge($data, [
                'relationship' => $fragments['general']->relationship->label ?? null,
                'category' => $context->place->category ?? null,
                'label' => $context->place ? $context->place->label : sprintf('%s (niet gekoppeld)', $context->label),
                'sections' => $context->sections->pluck('label')->toArray(),
                'circumstances' => $fragments['circumstances'],
                'contact' => $fragments['contact'],
                'general' => $fragments['general'],
                'moments' => collect($fragments['general']->moments)->sortBy('day')->map(function (Moment $moment) use ($case) {
                    $moment->source = $this->isSourceMoment($case, $moment);
                    $moment->formatted = $moment->day ? $moment->day->format('d-m-Y') : null;

                    return $moment;
                }),
                'contagious_period' => collect($fragments['general']->moments)->contains(static function (Moment $moment) use ($case) {
                    $start = $case->calculateContagiousPeriodStart();
                    $end = CarbonImmutable::now();

                    if ($moment->day === null || $start === null) {
                        return false;
                    }

                    return CarbonImmutable::parse($moment->day)->isBetween($start, $end);
                }),
                'source_period' => collect($fragments['general']->moments)->contains(static function (Moment $moment) use ($case) {
                    $start = $case->calculateSourcePeriodStart();
                    $end = $case->calculateSourcePeriodEnd();

                    if ($moment->day === null || $start === null || $end === null) {
                        return false;
                    }

                    return CarbonImmutable::parse($moment->day)->isBetween($start, $end);
                }),
            ]);
        });
    }

    private function isSourceMoment(CovidCase $case, Moment $moment): bool
    {
        $start = $case->calculateSourcePeriodStart();
        $end = $case->calculateSourcePeriodEnd();

        if ($moment->day === null || $start === null || $end === null) {
            return false;
        }

        return CarbonImmutable::parse($moment->day)->isBetween($start, $end);
    }
}
