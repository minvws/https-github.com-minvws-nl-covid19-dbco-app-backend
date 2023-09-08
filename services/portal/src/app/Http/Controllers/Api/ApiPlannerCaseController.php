<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\DataWithValidationResult;
use App\Dto\TimelineDto;
use App\Helpers\AuditObjectHelper;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Requests\Api\PlannerCase\CountRequest;
use App\Http\Requests\Api\PlannerCase\ListRequest;
use App\Http\Requests\Api\PlannerCase\PlannerSearchRequest;
use App\Http\Requests\Api\PlannerCase\UpdateMetaRequest;
use App\Http\Requests\Api\PlannerCase\UpdatePriorityRequest;
use App\Http\Requests\PlannerCaseCreateRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\PlannerCase\CovidCaseEncoder;
use App\Http\Responses\Timeline\TimelineDtoEncoder;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\CovidCase\General;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\PlannerCase;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\PlannerCase\ListOptions;
use App\Models\ValueObjects\CaseIdentifier;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnServiceException;
use App\Schema\Fragment;
use App\Schema\FragmentModel;
use App\Schema\Types\SchemaType;
use App\Services\AuthenticationService;
use App\Services\BcoNumber\BcoNumberException;
use App\Services\Bsn\BsnService;
use App\Services\CasePriorityService;
use App\Services\CaseService;
use App\Services\Note\CaseNoteService;
use App\Services\RefererService;
use App\Services\Timeline\TimelineService;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use Webmozart\Assert\Assert;

use function abort;
use function abort_if;
use function array_diff;
use function array_key_exists;
use function array_map;
use function count;

/**
 * NOTE:
 * The createCase, getCase and updateCase methods predate the countCases and listCases method and don't use custom
 * request objects and Eloquent models directly yet. This should be refactored when castable fragments have landed.
 */
class ApiPlannerCaseController extends ApiController
{
    use ValidatesModels;

    public function __construct(
        private readonly CaseService $caseService,
        private readonly AuthenticationService $authService,
        private readonly BsnService $bsnService,
        private readonly CasePriorityService $casePriorityService,
        private readonly CaseNoteService $caseNoteService,
        private readonly TimelineService $timelineService,
        private readonly ResponseFactory $response,
    ) {
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case aangemaakt')]
    public function createCase(PlannerCaseCreateRequest $request, AuditEvent $event): EncodableResponse
    {
        $data = $this->validateCreateCaseData($request->json()->all(), $validationResult);
        $data = $this->addSchemaVersions($data);
        $case = $this->decodeCase($data);

        $assignToCurrentUser = !RefererService::originatesFromCovidCaseOverviewPlannerPage($request);

        try {
            $createdCase = $this->caseService->createPlannerCase($case, $assignToCurrentUser);
        } catch (BcoNumberException) {
            abort(400, 'Could not create case because a new BCO Portal number could not be generated.');
        }

        $notes = $request->getNotes();
        if ($notes !== null) {
            $this->caseNoteService->createNote(
                $createdCase->uuid,
                CaseNoteType::caseAdded(),
                $notes,
                $this->authService->getAuthenticatedUser(),
            );
        }

        $eloquentCase = $this->caseService->getCaseByUuid($createdCase->uuid);
        Assert::notNull($eloquentCase);

        $auditObject = AuditObject::create('case', $createdCase->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($auditObject, $eloquentCase);
        $event->object($auditObject);

        if (!empty($case->caseLabels)) {
            $auditObject->detail('labelsUpdated', true);
        }
        if ($case->priority !== null) {
            $auditObject->detail('priorityUpdated', true);
        }

        $case = $this->getPlannerCase($createdCase->uuid);

        return new EncodableResponse(new DataWithValidationResult($case, $validationResult), 201);
    }

    /**
     * @throws ValueTypeMismatchException
     * @throws ValidationException
     * @throws Exception
     */
    #[SetAuditEventDescription('Case opgehaald voor planner')]
    public function getCase(EloquentCase $eloquentCase, AuditEvent $auditEvent): EncodableResponse
    {
        $this->setAuditObjectOrganisation($eloquentCase, $auditEvent);

        $plannerCase = $this->getPlannerCase($eloquentCase->uuid);

        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        $caseData = $encoder->encode($plannerCase);

        $rules = PlannerCase::fetchValidationRules($caseData);
        $validatedData = null;
        $validationResult = $this->validateModelRules(
            $caseData,
            $rules,
            PlannerCase::class,
            null,
            Validatable::SEVERITY_LEVEL_FATAL,
            $validatedData,
        );
        return new EncodableResponse(new DataWithValidationResult($plannerCase, $validationResult));
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case bijgewerkt')]
    public function updateCase(EloquentCase $eloquentCase, Request $request, AuditEvent $auditEvent): EncodableResponse
    {
        $this->setAuditObjectOrganisation($eloquentCase, $auditEvent);
        $plannerCase = $this->getPlannerCase($eloquentCase->uuid);

        $receivedData = $request->json()->all();
        $validatedData = $this->validateUpdateCaseData($receivedData, $plannerCase, $validationResult);
        $this->handleUpdateCaseAudit($eloquentCase, $validatedData, $auditEvent);

        $updatedCase = $this->decodeCase($validatedData, $plannerCase);
        if (!$this->updatePlannerCase($updatedCase)) {
            abort(500); // should not happen
        }

        return new EncodableResponse(new DataWithValidationResult($updatedCase, $validationResult));
    }

    /**
     * @throws Exception
     */
    public function updateCaseMeta(
        EloquentCase $eloquentCase,
        UpdateMetaRequest $request,
        AuditEvent $auditEvent,
    ): EncodableResponse|JsonResponse {
        $this->setAuditObjectOrganisation($eloquentCase, $auditEvent);
        $plannerCase = $this->getPlannerCase($eloquentCase->uuid);

        $validatedData = $request->validated();
        Assert::isArray($validatedData);
        $this->handleUpdateCaseAudit($eloquentCase, $validatedData, $auditEvent);

        $updatedCase = $this->decodeCase($validatedData, $plannerCase);
        if (!$this->updatePlannerCase($updatedCase)) {
            return $this->response->json(['error' => 'Error when updating case'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new EncodableResponse(new DataWithValidationResult($updatedCase, []));
    }

    #[SetAuditEventDescription('Case verwijderd')]
    public function deleteCase(EloquentCase $case): JsonResponse
    {
        $this->caseService->deleteCase($case);

        return $this->response->json('', 204);
    }

    public function countCases(CountRequest $request): EncodableResponse
    {
        return new EncodableResponse($this->caseService->getPlannerViewCounts($request->caseList));
    }

    /**
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Cases opgehaald door werkverdeler')]
    public function listCases(
        ListRequest $request,
        AuditEvent $event,
        CovidCaseEncoder $caseEncoder,
    ): EncodableResponse {
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);

        $list = $this->caseService->getPlannerViewCases($options);

        $event->objects(array_map(static fn ($c) => AuditObject::create('case', $c->uuid), $list->items()));

        return
            EncodableResponseBuilder::create($list)
            ->withContext(static function (EncodingContext $context) use ($caseEncoder): void {
                $context->registerDecorator(EloquentCase::class, $caseEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Case opgezocht met ID')]
    public function searchCase(
        PlannerSearchRequest $request,
        CovidCaseEncoder $caseEncoder,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $auditObject = AuditObject::create("case", 'identifier: ' . $request->getIdentifier());
        $auditEvent->object($auditObject);

        $case = $this->caseService->getCaseByIdentifierForOrganisation(
            new CaseIdentifier($request->getIdentifier()),
            $this->authService->getRequiredSelectedOrganisation()->uuid,
        );
        abort_if($case === null, 404);

        $auditObject->detail('uuid', $case->uuid);
        $auditObject->detail('hpzoneId', $case->hpzoneNumber);
        $auditObject->detail('caseId', $case->caseId);

        return EncodableResponseBuilder::create($case)
            ->withContext(static function (EncodingContext $context) use ($caseEncoder): void {
                    $context->registerDecorator(EloquentCase::class, $caseEncoder);
            })
            ->build();
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case prioriteit bijgewerkt')]
    public function updatePriority(UpdatePriorityRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        foreach ($request->getCaseUuids() as $caseUuid) {
            $auditEvent->object(AuditObject::create('case', $caseUuid)
                ->detail('priority', $request->getPriority()->value));
        }

        $this->casePriorityService->updateCasePriority($request->getCaseUuids(), $request->getPriority());

        return $this->response->json('', Response::HTTP_NO_CONTENT);
    }

    public function getTimeline(EloquentCase $case, TimelineDtoEncoder $timelineEncoder): EncodableResponse
    {
        $timeline = $this->timelineService->getTimeline($case);
        $timelineDtos = $this->timelineService->timelinesToDto($timeline);

        return
            EncodableResponseBuilder::create($timelineDtos)
            ->withContext(static function (EncodingContext $context) use ($timelineEncoder): void {
                    $context->registerDecorator(TimelineDto::class, $timelineEncoder);
            })
                ->build();
    }

    public function getPlannerTimeline(EloquentCase $case, TimelineDtoEncoder $timelineEncoder): EncodableResponse
    {
        $timeline = $this->timelineService->getPlannerTimeline($case);
        $timelineDtos = $this->timelineService->timelinesToDto($timeline);

        return
            EncodableResponseBuilder::create($timelineDtos)
            ->withContext(static function (EncodingContext $context) use ($timelineEncoder): void {
                    $context->registerDecorator(TimelineDto::class, $timelineEncoder);
            })
                ->build();
    }

    /**
     * @throws Exception
     */
    private function getPlannerCase(string $uuid): PlannerCase
    {
        return $this->caseService->getPlannerCase($uuid);
    }

    /**
     * @throws Exception
     */
    private function updatePlannerCase(PlannerCase $plannerCase): bool
    {
        if ($plannerCase->uuid === null) {
            return false;
        }

        $this->caseService->updatePlannerCase($plannerCase);
        return true;
    }

    /**
     * @throws ValueTypeMismatchException
     * @throws ValidationException
     */
    private function validateCreateCaseData(array $receivedData, ?array &$validationResult): array
    {
        $validatedData = null;
        $rules = PlannerCase::createValidationRules($receivedData);
        $validationResult = $this->validateModelRules(
            $receivedData,
            $rules,
            PlannerCase::class,
            null,
            Validatable::SEVERITY_LEVEL_FATAL,
            $validatedData,
        );

        $organisation = $this->authService->getRequiredSelectedOrganisation();
        if (array_key_exists('pseudoBsnGuid', $validatedData)) {
            try {
                $this->bsnService->getByPseudoBsnGuid($validatedData['pseudoBsnGuid'], $organisation->external_id);
            } catch (BsnException $exception) {
                $this->updateValidationResult($validationResult, 'pseudoBsnGuid', 'No BSN found with given value');
            } catch (BsnServiceException $exception) {
                $this->updateValidationResult($validationResult, 'pseudoBsnGuid', 'BSN service not available');
            }
        }

        if (isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            abort(new JsonResponse(['validationResult' => $validationResult], 422));
        }

        return $validatedData;
    }

    /**
     * @throws ValueTypeMismatchException
     * @throws ValidationException
     */
    private function validateUpdateCaseData(array $receivedData, PlannerCase $case, ?array &$validationResult): array
    {
        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);

        $validatedData = null;
        $rules = PlannerCase::updateValidationRules($receivedData, $case->uuid);
        $validationResult = $this->validateModelRules(
            $receivedData,
            $rules,
            PlannerCase::class,
            null,
            Validatable::SEVERITY_LEVEL_FATAL,
            $validatedData,
        );

        $organisation = $this->authService->getRequiredSelectedOrganisation();
        if (array_key_exists('pseudoBsnGuid', $validatedData) && ($validatedData['pseudoBsnGuid'] !== $case->pseudoBsnGuid)) {
            try {
                $this->bsnService->getByPseudoBsnGuid($validatedData['pseudoBsnGuid'], $organisation->external_id);
            } catch (BsnException) {
                $this->updateValidationResult($validationResult, 'pseudoBsnGuid', 'No BSN found with given value');
            } catch (BsnServiceException) {
                $this->updateValidationResult($validationResult, 'pseudoBsnGuid', 'BSN service not available');
            }
        }

        if (isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            abort(new JsonResponse(['validationResult' => $validationResult], 422));
        }
        return $validatedData;
    }

    /**
     * @throws CodableException
     */
    private function decodeCase(array $validatedCaseData, ?PlannerCase $current = null): PlannerCase
    {
        $decoder = new Decoder();

        if (array_key_exists('priority', $validatedCaseData)) {
            $validatedCaseData['priority'] = (int) $validatedCaseData['priority'];
        }

        return $decoder->decode($validatedCaseData)->decodeObject(PlannerCase::class, $current);
    }

    private function addSchemaVersions(array $data): array
    {
        $eloquentCaseSchema = EloquentCase::getSchema()->getCurrentVersion();
        $getFieldVersion = static fn (string $field): int => $eloquentCaseSchema
            ->getExpectedField($field)
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        $data = $this->addVersion($data, Index::class, 'index', $getFieldVersion('index'));
        $data = $this->addVersion($data, Contact::class, 'contact', $getFieldVersion('contact'));
        $data = $this->addVersion($data, Test::class, 'test', $getFieldVersion('test'));
        $data = $this->addVersion($data, General::class, 'general', $getFieldVersion('general'));

        return $data;
    }

    /**
     * @template T of Fragment|FragmentModel
     *
     * @param class-string<T> $class
     */
    private function addVersion(array $data, string $class, string $index, int $version): array
    {
        if (!array_key_exists($index, $data)) {
            return $data;
        }

        $versionField = $class::getSchema()->getSchemaVersionField()?->getName();
        Assert::string($versionField);

        $data[$index][$versionField] = $data[$index][$versionField] ?? $version;

        return $data;
    }

    private function handleUpdateCaseAudit(
        EloquentCase $eloquentCase,
        array $validatedData,
        AuditEvent $auditEvent,
    ): void {
        $auditObject = AuditObject::create('case', $eloquentCase->uuid);
        AuditObjectHelper::setAuditObjectOrganisation($auditObject, $eloquentCase);

        if (isset($validatedData['caseLabels']) && $this->labelsAreChanged($validatedData['caseLabels'], $eloquentCase)) {
            $auditObject->detail('labelsUpdated', true);
        }

        if (isset($validatedData['priority']) && (int) $validatedData['priority'] !== $eloquentCase->priority->value) {
            $auditObject->detail('priorityUpdated', true);
        }
        $auditEvent->object($auditObject);
    }

    private function labelsAreChanged(array $caseLabels, EloquentCase $eloquentCase): bool
    {
        $currentLabels = $eloquentCase->caseLabels->pluck('uuid')->all();
        return !empty(array_diff($caseLabels, $currentLabels)) || !empty(array_diff($currentLabels, $caseLabels));
    }

    private function updateValidationResult(array &$validationResult, string $key, string $message): void
    {
        if (array_key_exists(Validatable::SEVERITY_LEVEL_FATAL, $validationResult)) {
            /** @var MessageBag $errors */
            $errors = $validationResult[Validatable::SEVERITY_LEVEL_FATAL]['errors'];
            $errors->add($key, $message);
        } else {
            $validationResult[Validatable::SEVERITY_LEVEL_FATAL]['errors'] = new MessageBag([
                $key => $message,
            ]);
        }
    }

    private function setAuditObjectOrganisation(
        EloquentCase $eloquentCase,
        AuditEvent $auditEvent,
    ): void {
        if (count($auditEvent->getObjects()) === 0) {
            return;
        }

        AuditObjectHelper::setAuditObjectOrganisation($auditEvent->getObjects()[0], $eloquentCase);
    }
}
