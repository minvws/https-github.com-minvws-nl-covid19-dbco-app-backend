<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Exceptions\IntakeException;
use App\Helpers\AuditUserHelper;
use App\Helpers\FeatureFlagHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Intake;
use App\Models\Intake\ListOptions;
use App\Models\Intake\RawIntake;
use App\Repositories\CaseLabelRepository;
use App\Repositories\Intake\IntakeRepository;
use App\Services\CaseUpdate\CaseUpdateService;
use App\Services\MessageQueue\IncomingMessage;
use App\Services\MessageQueue\IncomingMessageProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LogicException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Psr\Log\LoggerInterface;
use Throwable;

use function count;
use function is_array;
use function is_null;
use function is_object;
use function sprintf;

class IntakeService implements IncomingMessageProcessor
{
    private const CASE_LABEL_REPEAT_RESULT_DIFF_IN_WEEKS = 8;

    public function __construct(
        private IntakeConfig $intakeConfig,
        private RawIntakeValidator $rawIntakeValidator,
        private CaseLabelRepository $caseLabelRepository,
        private IntakeConfirmationService $intakeConfirmationService,
        private IntakeRepository $intakeRepository,
        private CaseUpdateService $caseUpdateService,
        private IncomingMessageToRawIntakeConverter $incomingMessageToRawIntakeConverter,
        private LoggerInterface $logger,
        private RawIntakeToIntakeConverter $rawIntakeToIntakeConverter,
        private AuditService $auditService,
    ) {
    }

    /**
     * Process incoming message
     *
     * @throws IntakeException
     */
    #[SetAuditEventDescription('Verwerk inkomend bericht')]
    public function processIncomingMessage(IncomingMessage $incomingMessage): void
    {
        $intakeAuditObject = AuditObject::create('intake');
        $this->auditService->registerEvent(
            AuditEvent::create(
                __METHOD__,
                AuditEvent::ACTION_EXECUTE,
                AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
            )
                ->object($intakeAuditObject),
            fn (AuditEvent $auditEvent) => $this->doProcessIncomingMessage($incomingMessage, $intakeAuditObject)
        );
        $this->auditService->finalizeEvent();
    }

    private function doProcessIncomingMessage(IncomingMessage $incomingMessage, AuditObject $intakeAuditObject): void
    {
        $this->auditService->getCurrentEvent()
            ?->user(AuditUserHelper::getAuditUser());

        try {
            $intakeAuditObject->identifier($incomingMessage->getId());
            $rawIntake = $this->incomingMessageToRawIntakeConverter->convert($incomingMessage);
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Error processing incoming message "%s": %s', $incomingMessage->getId(), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw IntakeException::fromThrowable($e);
        }

        $this->processRawIntake($rawIntake, $intakeAuditObject);
    }

    /**
     * @throws IntakeException
     */
    public function processRawIntake(RawIntake $rawIntake, AuditObject $intakeAuditObject): void
    {
        $rawIntake = $this->validateRawIntake($rawIntake);
        $rawIntake = $this->mergeIntakeData($rawIntake);

        DB::transaction(function () use ($rawIntake, &$intake): void {
            $intake = $this->rawIntakeToIntakeConverter->convert($rawIntake);
            $this->addFragmentsToIntake($intake, $rawIntake);
            $this->addContactsToIntake($intake, $rawIntake);
            $this->addCaseLabelsToIntake($intake, $rawIntake);
        });

        if (!$intake instanceof Intake) {
            // should not be possible
            throw new IntakeException('Failed to create intake');
        }

        $intakeAuditObject->detail('type', $intake->type);
        $intakeAuditObject->detail('source', $intake->source);
        $intakeAuditObject->detail('identifierType', $intake->identifier_type);
        $intakeAuditObject->detail('identifier', $intake->identifier);
        $intakeAuditObject->detail('pseudoBsnGuid', $intake->pseudo_bsn_guid);

        $this->matchIntakeToCase($intake, $intakeAuditObject);
        $this->intakeConfirmationService->confirmToIndex($intake);
    }

    /**
     * @throws IntakeException
     */
    protected function validateRawIntake(RawIntake $intake): RawIntake
    {
        return $this->rawIntakeValidator->validateRawIntake($intake);
    }

    private function mergeIntakeData(RawIntake $intake): RawIntake
    {
        $intakeData = $intake->getIntakeData();

        return new RawIntake(
            $intake->getId(),
            $intake->getType(),
            $intake->getSource(),
            $intake->getIdentityData(),
            $intakeData,
            $intake->getHandoverData(),
            $intake->getReceivedAt(),
        );
    }

    private function addFragmentsToIntake(Intake $intake, RawIntake $rawIntake): void
    {
        $this->logger->info(sprintf('Adding fragments to intake "%s"...', $intake->uuid));

        $intakeData = $rawIntake->getIntakeData();

        foreach ($this->intakeConfig->getAllowedCaseFragments() as $fragmentName) {
            $fragmentData = $intakeData[$fragmentName] ?? null;
            if ($fragmentData === null) {
                continue;
            }

            $this->logger->info(sprintf('Creating fragment "%s" for intake "%s"...', $fragmentName, $intake->uuid));
            $fragment = $this->intakeRepository->makeIntakeFragmentForIntake($intake);
            $fragment->name = $fragmentName;
            $fragment->data = $fragmentData;
            $fragment->version = $this->intakeConfig->getVersionForCaseFragment($fragmentName);
            $this->intakeRepository->saveIntakeFragmentForIntake($fragment, $intake);
        }
    }

    private function addContactsToIntake(Intake $intake, RawIntake $rawIntake): void
    {
        $this->logger->info(sprintf('Adding contacts to intake "%s"...', $intake->uuid));

        $intakeData = $rawIntake->getIntakeData();
        if (empty($intakeData['contacts']) || !is_array($intakeData['contacts'])) {
            $this->logger->info(sprintf('No contacts found for intake "%s"...', $intake->uuid));
            return;
        }

        foreach ($intakeData['contacts'] as $contactData) {
            $this->logger->info(sprintf('Creating intake contact for intake "%s"...', $intake->uuid));
            $intakeContact = $this->intakeRepository->makeIntakeContactForIntake($intake);
            $this->intakeRepository->saveIntakeContactForIntake($intakeContact, $intake);

            foreach ($this->intakeConfig->getAllowedContactFragments() as $fragmentName) {
                $fragmentData = $contactData[$fragmentName] ?? null;
                if ($fragmentData === null) {
                    continue;
                }

                $this->logger->info(sprintf('Creating intake contact fragment "%s" for intake "%s"...', $fragmentName, $intake->uuid));
                $fragment = $this->intakeRepository->makeIntakeContactFragmentForIntake($intakeContact);
                $fragment->name = $fragmentName;
                $fragment->data = $fragmentData;
                $fragment->version = $this->intakeConfig->getVersionForContactFragment($fragmentName);
                $this->intakeRepository->saveIntakeContactFragmentForIntake($fragment, $intakeContact);
            }
        }
    }

    private function addCaseLabelsToIntake(Intake $intake, RawIntake $rawIntake): void
    {
        $this->logger->info(sprintf('Adding labels to intake "%s"...', $intake->uuid));

        $rawIntakeData = $rawIntake->getIntakeData();
        $caseLabelCodesToAttach = new Collection([
            'intake_submitted',
        ]);

        $hasUnderlyingSuffering = $rawIntakeData['underlyingSuffering']['hasUnderlyingSuffering'] ?? null;
        $underlyingSufferingItems = $rawIntakeData['underlyingSuffering']['items'] ?? [];
        if ($hasUnderlyingSuffering === null || $hasUnderlyingSuffering === YesNoUnknown::unknown()->value) {
            $caseLabelCodesToAttach->push('incomplete_data');
        } elseif ($hasUnderlyingSuffering === YesNoUnknown::yes()->value && count($underlyingSufferingItems) > 0) {
            $caseLabelCodesToAttach->push('health_indication');
        }

        $isPregnant = $rawIntakeData['pregnancy']['isPregnant'] ?? null;
        if ($isPregnant === YesNoUnknown::yes()->value) {
            $caseLabelCodesToAttach->push('health_indication');
        }

        $hasRecentlyGivenBirth = $rawIntakeData['recentBirth']['hasRecentlyGivenBirth'] ?? null;
        if ($hasRecentlyGivenBirth === YesNoUnknown::yes()->value) {
            $caseLabelCodesToAttach->push('health_indication');
        }

        $previousInfectionDateOfSymptom = $rawIntakeData['test']['previousInfectionDateOfSymptom'] ?? null;

        try {
            $previousInfectionDateOfSymptomDate = CarbonImmutable::createFromFormat('Y-m-d', $previousInfectionDateOfSymptom);
        } catch (Throwable $exception) {
            $previousInfectionDateOfSymptomDate = false;
        }

        if (
            $previousInfectionDateOfSymptom !== null
            && $intake->date_of_symptom_onset !== null
            && $previousInfectionDateOfSymptomDate !== false
            && $previousInfectionDateOfSymptomDate->diffInWeeks($intake->date_of_test) < self::CASE_LABEL_REPEAT_RESULT_DIFF_IN_WEEKS
        ) {
            $caseLabelCodesToAttach->push('repeat_result');
        }

        $wasAbroad = $rawIntakeData['abroad']['wasAbroad'] ?? null;
        if ($wasAbroad === YesNoUnknown::yes()->value) {
            $caseLabelCodesToAttach->push('abroad');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['sourceEnvironments']['likelySourceEnvironments'] ?? [],
                [
                    ContextCategory::kinderOpvang()->value,
                    ContextCategory::basisOnderwijs()->value,
                    ContextCategory::voortgezetOnderwijs()->value,
                    ContextCategory::mbo()->value,
                    ContextCategory::hboUniversiteit()->value,
                ],
            )
        ) {
            $caseLabelCodesToAttach->push('school');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['sourceEnvironments']['likelySourceEnvironments'] ?? [],
                [ContextCategory::zeeTransport()],
            )
        ) {
            $caseLabelCodesToAttach->push('shipping_person');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['sourceEnvironments']['likelySourceEnvironments'] ?? [],
                [ContextCategory::vliegTransport()],
            )
        ) {
            $caseLabelCodesToAttach->push('flights');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['sourceEnvironments']['likelySourceEnvironments'] ?? [],
                [
                    ContextCategory::verpleeghuis(),
                    ContextCategory::ziekenhuis(),
                    ContextCategory::huisarts(),
                    ContextCategory::thuiszorg(),
                ],
            )
        ) {
            $caseLabelCodesToAttach->push('healthcare');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['job']['sectors'] ?? [],
                [
                    JobSector::andereZorg(),
                    JobSector::ziekenhuis(),
                    JobSector::verpleeghuisOfVerzorgingshuis(),
                    JobSector::mantelzorg(),
                ],
            )
        ) {
            $caseLabelCodesToAttach->push('healthcare_employee');
        }

        if (
            $this->arrayContainsAny(
                $rawIntakeData['job']['sectors'] ?? [],
                [
                    JobSector::dagopvang(),
                    JobSector::basisschoolEnBuitenschoolseOpvang(),
                    JobSector::middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs(),
                    JobSector::medewerkerHogerOnderwijs(),
                ],
            )
        ) {
            $caseLabelCodesToAttach->push('school');
        }

        $intake->caseLabels()->attach($this->caseLabelRepository->getLabelsByCode($caseLabelCodesToAttach->unique()));
    }

    public function matchIntakeToCase(Intake $intake, AuditObject $intakeAuditObject): void
    {
        if (FeatureFlagHelper::isDisabled('intake_match_case_enabled')) {
            $this->logger->info(sprintf('Match intake "%s" to case disabled...', $intake->uuid));
            $intakeAuditObject->detail('matchCase', 'disabled');
            return;
        }

        $this->logger->info(sprintf('Trying to match intake "%s" to case...', $intake->uuid));

        // try to find a case based pseudoBsn and monsternummmer for which this intake can be applied
        // if a match is found, the intake data will be converted to a CaseUpdate. The BCO-er may choose to add
        // the CaseUpdate to the case. If found the Intake can be removed afterwards.

        $case = $this->intakeRepository->findCaseByIntake($intake);
        if (is_null($case)) {
            $this->logger->info(sprintf('No matching case found for intake "%s"', $intake->uuid));
            $intakeAuditObject->detail('matchCase', 'notFound');
            return;
        }

        $this->logger->info(sprintf('Matching case "%s" found for intake "%s"', $case->uuid, $intake->uuid));

        $caseUpdate = $this->caseUpdateService->convertIntakeToCaseUpdate($intake, $case);

        if (!is_object($caseUpdate)) {
            $this->logger->info('Error converting Intake to Case', [
                'intake' => $intake->uuid,
            ]);
            throw new LogicException('Error converting Intake to Case: ' . $intake->uuid);
        }

        $intakeAuditObject->detail('matchCase', 'found');
    }

    public function matchCaseToIntake(EloquentCase $case): void
    {
        if (FeatureFlagHelper::isDisabled('intake_match_case_enabled')) {
            $this->logger->info(sprintf('Match case "%s" to intake disabled...', $case->uuid));
            return;
        }

        $intake = $this->intakeRepository->findIntakeByCase($case);
        if (is_null($intake)) {
            // No case found matching the criteria
            return;
        }

        $caseUpdate = $this->caseUpdateService->convertIntakeToCaseUpdate($intake, $case);

        if (!is_object($caseUpdate)) {
            throw new LogicException('Error converting Intake to Case: ' . $intake->uuid);
        }
    }

    public function listIntakes(ListOptions $options): Paginator
    {
        return $this->intakeRepository->listIntakes($options);
    }

    private function arrayContainsAny(array $items, array $search): bool
    {
        $collection = new Collection($items);
        return $collection->intersect($search)->count() > 0;
    }
}
