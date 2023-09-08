<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ExpertQuestion\AnswerRequest;
use App\Http\Requests\Api\ExpertQuestion\FindByCaseIdRequest;
use App\Http\Requests\Api\ExpertQuestion\ListRequest;
use App\Http\Requests\Api\ExpertQuestion\PickupRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\ExpertQuestion\ExpertQuestionEncoder;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\ExpertQuestion\ExpertQuestion as ExpertQuestionValidationModel;
use App\Models\ExpertQuestion\ListOptions;
use App\Repositories\ExpertQuestionRepository;
use App\Services\AuthenticationService;
use App\Services\ExpertQuestion\ExpertQuestionService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Services\AuditService;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use function abort;
use function abort_if;
use function response;

class ApiExpertQuestionController extends Controller
{
    use ValidatesModels;

    protected AuditService $auditService;
    protected ExpertQuestionEncoder $expertQuestionEncoder;
    protected ExpertQuestionRepository $expertQuestionRepository;
    private AuthenticationService $authenticationService;
    private ExpertQuestionService $expertQuestionService;

    /**
     * Controller constructor
     */
    public function __construct(
        AuditService $auditService,
        AuthenticationService $authenticationService,
        ExpertQuestionEncoder $expertQuestionEncoder,
        ExpertQuestionService $expertQuestionService,
    ) {
        $this->auditService = $auditService;
        $this->authenticationService = $authenticationService;
        $this->expertQuestionEncoder = $expertQuestionEncoder;
        $this->expertQuestionService = $expertQuestionService;
    }

    /**
     * List expert questions
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Lijst van hulpvragen opvragen')]
    public function listExpertQuestions(
        ListRequest $request,
        ExpertQuestionEncoder $expertQuestionEncoder,
    ): EncodableResponse {
        /** @var ListOptions $options */
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);
        $paginator = $this->expertQuestionService->listExpertQuestions($options);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            static fn (AuditEvent $auditEvent) => EncodableResponseBuilder::create($paginator, Response::HTTP_OK)
                ->withContext(static function (EncodingContext $context) use ($expertQuestionEncoder): void {
                    $context->registerDecorator(ExpertQuestion::class, $expertQuestionEncoder);
                })
                ->build()
        );
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Hulpvraag details ophalen')]
    public function getExpertQuestion(ExpertQuestion $expertQuestion): EncodableResponse
    {
        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableResponse($expertQuestion)
        );
    }

    #[SetAuditEventDescription('Nieuwe supervisie vraag toegevoegd')]
    public function create(Request $request, EloquentCase $case, ExpertQuestionEncoder $expertQuestionEncoder): EncodableResponse
    {
        $data = $this->validateRequest($request);
        $type = ExpertQuestionType::from($data['type']);

        try {
            $expertQuestion = $this->expertQuestionService->createExpertQuestion(
                $case,
                $this->authenticationService->getAuthenticatedUser(),
                $type,
                $data['subject'],
                $data['phone'] ?? null,
                $data['question'],
            );
        } catch (AuthenticationException $e) {
            abort(BaseResponse::HTTP_FORBIDDEN);
        }

        return $this->encodeQuestion($expertQuestion, BaseResponse::HTTP_CREATED, $expertQuestionEncoder);
    }

    #[SetAuditEventDescription('Supervisie vraag bijgewerkt')]
    public function update(
        Request $request,
        EloquentCase $case,
        ExpertQuestion $expertQuestion,
        ExpertQuestionEncoder $expertQuestionEncoder,
    ): EncodableResponse {
        abort_if($case->uuid !== $expertQuestion->case_uuid, BaseResponse::HTTP_BAD_REQUEST, 'Case mismatch');

        $data = $this->validateRequest($request);
        $type = ExpertQuestionType::from($data['type']);

        $expertQuestion = $this->expertQuestionService->updateExpertQuestion(
            $expertQuestion,
            $type,
            $data['subject'],
            $data['phone'] ?? null,
            $data['question'],
        );

        return $this->encodeQuestion($expertQuestion, BaseResponse::HTTP_OK, $expertQuestionEncoder);
    }

    /**
     * Answer expert question
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Hulpvraag beantwoorden')]
    public function answerExpertQuestion(ExpertQuestion $expertQuestion, AnswerRequest $request): EncodableResponse
    {
        if ($expertQuestion->hasAnswer()) {
            throw new UnprocessableEntityHttpException('Expert question already has an answer');
        }

        /**
         * @var string $userUuid
         */
        $userUuid = Auth::id();

        $expertQuestion = $this->expertQuestionService->answerExpertQuestion($expertQuestion, $request->getAnswer(), $userUuid);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_UPDATE, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableResponse($expertQuestion->refresh())
        );
    }

    /**
     * Pickup expert question
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Hulpvraag oppakken')]
    public function assignExpertQuestion(ExpertQuestion $expertQuestion, PickupRequest $request): EncodableResponse
    {
        // Should be changed in the future if there is a request that we will be able to assign questions to other experts
        if ($request->getAssignUserUuid() !== Auth::id()) {
            throw new Exception('Cannot assign expert question to someone else / You can only assign expert questions to yourself', 422);
        }

        $expertQuestion = $this->expertQuestionService->assignExpertQuestion($expertQuestion, $request->getAssignUserUuid());

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_UPDATE, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableResponse($expertQuestion)
        );
    }

    /**
     * @return EncodableResponse
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Navigeren naar hulpvraag op basis van case id')]
    public function findExpertQuestionByCaseId(FindByCaseIdRequest $request): BaseResponse
    {
        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            function (AuditEvent $auditEvent) use ($request) {
                $caseId = $request->getPostCaseId();
                $expertQuestionType = $request->getExpertQuestionType();

                if ($caseId === null) {
                    return response()->json(['error' => 'Case ID not found'], BaseResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($expertQuestionType === null) {
                    return response()->json(['error' => 'Expert question type not found'], BaseResponse::HTTP_UNPROCESSABLE_ENTITY);
                }

                $expertQuestion = $this->expertQuestionService->getExpertQuestionByTypeAndCaseId($caseId, $expertQuestionType);

                if ($expertQuestion === null) {
                    return response()->json(['error' => 'Geen hulpvraag gevonden voor deze case'], BaseResponse::HTTP_NOT_FOUND);
                }

                if ($expertQuestion->assigned_user_uuid !== null && $expertQuestion->assigned_user_uuid !== Auth::id()) {
                    return response()->json(
                        ['error' => 'Deze medische supervisie hulpvraag is al opgepakt door iemand anders'],
                        BaseResponse::HTTP_CONFLICT,
                    );
                }

                return $this->encodableResponse($expertQuestion);
            },
        );
    }

    /**
     * Unassign expert question
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Opgepakte hulpvraag teruggeven')]
    public function unassignExpertQuestion(ExpertQuestion $expertQuestion): EncodableResponse
    {
        $expertQuestion = $this->expertQuestionService->unassignExpertQuestion($expertQuestion);

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(__METHOD__, AuditEvent::ACTION_READ, AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__)),
            fn (AuditEvent $auditEvent) => $this->encodableResponse($expertQuestion)
        );
    }

    /**
     * Encode the response
     */
    protected function encodableResponse(mixed $data): EncodableResponse
    {
        return EncodableResponseBuilder::create($data)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(ExpertQuestion::class, $this->expertQuestionEncoder);
            })->build();
    }

    private function encodeQuestion(
        ExpertQuestion $expertQuestion,
        int $status,
        ExpertQuestionEncoder $expertQuestionEncoder,
    ): EncodableResponse {
        return
            EncodableResponseBuilder::create($expertQuestion, $status)
            ->withContext(static function (EncodingContext $context) use ($expertQuestionEncoder): void {
                    $context->registerDecorator(ExpertQuestion::class, $expertQuestionEncoder);
            })
                ->build();
    }

    private function validateRequest(Request $request): array
    {
        $validatedData = null;
        $validationResult = $this->validateModel(
            ExpertQuestionValidationModel::class,
            $request->json()->all(),
            $validatedData,
        );

        if (isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            abort(new JsonResponse(['validationResult' => $validationResult], 422));
        }

        return $validatedData;
    }
}
