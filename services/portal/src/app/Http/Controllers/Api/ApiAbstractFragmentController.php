<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\FragmentNotAccessibleException;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentBaseModel;
use App\Schema\SchemaObject;
use App\Schema\Types\SchemaType;
use App\Services\FragmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;
use MinVWS\Codable\ValueTypeMismatchException;
use RuntimeException;

use function array_diff;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function explode;
use function in_array;
use function is_array;
use function lcfirst;
use function str_replace;
use function strlen;
use function trim;
use function ucwords;

use const JSON_PRETTY_PRINT;

/**
 * Fragment base controller.
 */
abstract class ApiAbstractFragmentController extends ApiController
{
    protected FragmentService $fragmentService;

    /**
     * Fragment service.
     */
    protected function __construct(FragmentService $fragmentService)
    {
        $this->fragmentService = $fragmentService;
    }

    /**
     * @param array<int, string> $fragmentNames
     */
    abstract protected function objectForAuditEvent(string $ownerUuid, array $fragmentNames): AuditObject;

    /**
     * Create audit event.
     *
     * @param string $function Function name.
     * @param string $action Audit action.
     * @param string $ownerUuid Owner entity identifier.
     * @param array $fragmentNames Fragment names.
     */
    protected function createAuditEvent(
        string $function,
        string $action,
        string $ownerUuid,
        array $fragmentNames,
    ): AuditEvent {
        $method = static::class . '::' . $function;
        $auditEvent = AuditEvent::create($method, $action, AuditEventHelper::getAuditEventDescriptionByActionName($method));
        $auditEvent->object($this->objectForAuditEvent($ownerUuid, $fragmentNames));
        return $auditEvent;
    }

    /**
     * Transforms a fragment name from 'verb1-verb2-verb3' to 'verb1Verb2Verb3'.
     */
    private function parseFragmentName(string $fragmentName): string
    {
        return lcfirst(str_replace('-', '', ucwords($fragmentName, '-')));
    }

    /**
     * Prase fragment names.
     *
     * @return array|null
     */
    private function parseFragmentNames(?string $fragmentNames): ?array
    {
        if ($fragmentNames === null || strlen(trim($fragmentNames)) === 0) {
            return null;
        }

        return array_map(fn($n) => $this->parseFragmentName($n), explode(',', trim($fragmentNames)));
    }

    /**
     * Validate request arguments.
     *
     * @param array $fragmentNames
     */
    private function validateRequestArgs(array $fragmentNames, bool $singleFragment, ?JsonResponse &$errorResponse = null): bool
    {
        if (count($fragmentNames) === 0) {
            $responseData = ['code' => 'invalid', 'message' => 'No fragments specified!'];
            $errorResponse = new JsonResponse($responseData, Response::HTTP_BAD_REQUEST, [], JSON_PRETTY_PRINT);
            return false;
        }

        if ($singleFragment && !in_array($fragmentNames[0], $this->fragmentService::fragmentNames(), true)) {
            $responseData = ['code' => 'notFound', 'message' => 'Fragment not found! '];
            $errorResponse = new JsonResponse($responseData, Response::HTTP_NOT_FOUND, [], JSON_PRETTY_PRINT);
            return false;
        }

        if (!$singleFragment && count(array_diff($fragmentNames, $this->fragmentService::fragmentNames())) > 0) {
            $responseData = ['code' => 'invalid', 'message' => 'One or more invalid fragments specified!'];
            $errorResponse = new JsonResponse($responseData, Response::HTTP_BAD_REQUEST, [], JSON_PRETTY_PRINT);
            return false;
        }

        return true;
    }

    /**
     * Validate request body.
     *
     * @param EloquentBaseModel $owner Entity instance.
     * @param array $fragmentNames Fragment names.
     * @param bool $singleFragment Single fragment mode.
     * @param mixed $data Data in body of request.
     * @param array|null $validationResult Contains the validation results.
     * @param array|null $validatedData Validated data.
     * @param JsonResponse|null $errorResponse Contains the error response if the data contains fatal errors.
     */
    private function validateRequestBody(EloquentBaseModel $owner, array $fragmentNames, bool $singleFragment, mixed $data, ?array &$validationResult = null, ?array &$validatedData = null, ?JsonResponse &$errorResponse = null): bool
    {
        $validationResult = $this->fragmentService->validateFragments($owner, $fragmentNames, $data, $validatedData);

        foreach ($validationResult as $fragmentValidationResult) {
            if ($singleFragment && !empty($fragmentValidationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
                $responseData = ['validationResult' => $fragmentValidationResult];
                $errorResponse = new JsonResponse($responseData, Response::HTTP_BAD_REQUEST, [], JSON_PRETTY_PRINT);
                return false;
            }

            if (!empty($fragmentValidationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
                $responseData = ['validationResult' => $validationResult];
                $errorResponse = new JsonResponse($responseData, Response::HTTP_BAD_REQUEST, [], JSON_PRETTY_PRINT);
                return false;
            }
        }

        return true;
    }

    /**
     * Build fragment response.
     *
     * @param array $fragments Fragments indexed by name.
     * @param bool $singleFragment Response only contains a single fragment (no fragment name prefixing).
     * @param array|null $validationResult Validation result. If null, this method validates the fragment data itself.
     * @param int $statusCode Status code.
     *
     * @throws ValueTypeMismatchException
     */
    private function responseForFragments(
        EloquentBaseModel $owner,
        array $fragments,
        bool $singleFragment,
        ?array $validationResult = null,
        int $statusCode = 200,
    ): JsonResponse {
        $data = $this->fragmentService->encodeFragments($fragments);
        $validationResult = $validationResult ?? $this->fragmentService->validateFragments($owner, array_keys($fragments), $data);

        if ($singleFragment) {
            $fragmentName = array_keys($fragments)[0];

            $responseData = [
                'data' => $data[$fragmentName],
                'validationResult' => $validationResult[$fragmentName] ?? null,
            ];
        } else {
            $responseData = [
                'data' => $data,
                'validationResult' => array_filter($validationResult, static function ($v) {
                    // Ensure no empty arrays are returned
                    return count($v) !== 0;
                }),
            ];
        }

        if ($responseData['validationResult'] === null || count($responseData['validationResult']) === 0) {
            unset($responseData['validationResult']);
        }

        $owner->refresh();
        $computedData = $this->addComputedData($owner);
        if ($computedData) {
            $responseData['computedData'] = $computedData;
        }

        return new JsonResponse($responseData, $statusCode);
    }

    protected function addComputedData(EloquentBaseModel $owner): ?array
    {
        return null;
    }

    /**
     * Fetches a single fragment.
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Los fragment opgehaald')]
    public function getFragment(EloquentBaseModel $owner, string $fragmentName, AuditService $auditService): JsonResponse
    {
        return $this->handleGetFragments($owner, [$this->parseFragmentName($fragmentName)], true, $auditService, __FUNCTION__);
    }

    /**
     * Fetches the requested fragments.
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Fragmenten opgehaald')]
    public function getFragments(EloquentBaseModel $owner, Request $request, AuditService $auditService): JsonResponse
    {
        /** @var string|null $names */
        $names = $request->query('names');

        $fragmentNames = $this->parseFragmentNames($names) ?? $this->fragmentService::fragmentNames();

        return $this->handleGetFragments($owner, $fragmentNames, false, $auditService, __FUNCTION__);
    }

    /**
     * Fetches the requested fragments.
     *
     * @param array $fragmentNames
     *
     * @throws Exception
     */
    protected function handleGetFragments(
        EloquentBaseModel $owner,
        array $fragmentNames,
        bool $singleFragment,
        AuditService $auditService,
        string $auditEventCode,
    ): JsonResponse {
        $auditEvent = $this->createAuditEvent($auditEventCode, AuditEvent::ACTION_READ, $owner->uuid, $fragmentNames);

        return $auditService->registerHttpEvent($auditEvent, function () use ($owner, $fragmentNames, $singleFragment) {
            if (
                !$this->validateRequestArgs(
                    $fragmentNames,
                    $singleFragment,
                    $errorResponse,
                )
            ) {
                return $errorResponse;
            }

            try {
                $fragments = $this->fragmentService->loadFragments($owner->uuid, $fragmentNames);
                return $this->responseForFragments($owner, $fragments, $singleFragment);
            } catch (FragmentNotAccessibleException $exception) {
                return new JsonResponse(['error' => "Deze fragment bestaat niet (meer)"], Response::HTTP_NOT_FOUND);
            }
        });
    }

    /**
     * Updates single fragment.
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Fragment bijgewerkt')]
    public function updateFragment(
        EloquentBaseModel $owner,
        string $fragmentName,
        Request $request,
        AuditService $auditService,
    ): JsonResponse {
        $data = [$this->parseFragmentName($fragmentName) => $request->json()->all()];
        return $this->handleUpdateFragments($owner, $data, true, $auditService, __FUNCTION__);
    }

    /**
     * Updates the specified fragments.
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Fragments bijgewerkt')]
    public function updateFragments(
        EloquentBaseModel $owner,
        Request $request,
        AuditService $auditService,
    ): JsonResponse {
        $data = $request->json()->all();
        $data = is_array($data) ? $data : [];
        return $this->handleUpdateFragments($owner, $data, false, $auditService, __FUNCTION__);
    }

    /**
     * Updates the specified fragments.
     *
     * @param array $data
     *
     * @throws Exception
     */
    #[SetAuditEventDescription('Fragment bijgewerkt')]
    public function handleUpdateFragments(
        EloquentBaseModel $owner,
        array $data,
        bool $singleFragment,
        AuditService $auditService,
        string $auditEventCode,
    ): JsonResponse {
        $fragmentNames = array_keys($data);
        $auditEvent = $this->createAuditEvent($auditEventCode, AuditEvent::ACTION_UPDATE, $owner->uuid, $fragmentNames);

        return $auditService->registerHttpEvent($auditEvent, function () use ($owner, $fragmentNames, $singleFragment, $data) {
            if (
                !$this->validateRequestArgs(
                    $fragmentNames,
                    $singleFragment,
                    $errorResponse,
                )
            ) {
                return $errorResponse;
            }

            try {
                $this->enrichFragmentDataWithVersion($owner, $fragmentNames, $data);
            } catch (RuntimeException $e) {
                Log::error("Could not determine fragment version.", [$e->getMessage(), 'ownerUuid' => $owner->uuid]);
                return new JsonResponse(['error' => "Kon fragment versie niet bepalen."], Response::HTTP_BAD_REQUEST);
            }

            if (
                !$this->validateRequestBody(
                    $owner,
                    $fragmentNames,
                    $singleFragment,
                    $data,
                    $validationResult,
                    $validatedData,
                    $errorResponse,
                )
            ) {
                return $errorResponse;
            }

            try {
                $fragments = DB::transaction(function () use ($owner, $fragmentNames, $validatedData) {
                    $validatedData = array_filter($validatedData) ?? [];
                    $validatedFragmentNames = array_keys($validatedData);

                    // load existing fragments so we can do a partial update
                    $fragments = $this->fragmentService->loadFragments($owner->uuid, $fragmentNames);

                    // decode fragments
                    $updatedFragments = $this->fragmentService->decodeFragments($validatedFragmentNames, $validatedData, $fragments);

                    // no fatal errors, so safe to store
                    $this->fragmentService->storeFragments($owner->uuid, $updatedFragments);

                    return array_merge($fragments, $updatedFragments);
                }, 3);

                return $this->responseForFragments($owner, $fragments, $singleFragment, $validationResult);
            } catch (FragmentNotAccessibleException $exception) {
                return new JsonResponse(['error' => "Deze fragment bestaat niet (meer)"], Response::HTTP_NOT_FOUND);
            }
        });
    }

    private function enrichFragmentDataWithVersion(EloquentBaseModel $owner, array $fragmentNames, array &$data): void
    {
        if (!$owner instanceof SchemaObject) {
            throw new RuntimeException('Model is not a SchemaObject!');
        }

        $schemaVersion = $owner->getSchemaVersion();

        foreach ($fragmentNames as $fragmentName) {
            $field = $schemaVersion->getField($fragmentName);

            if ($field === null) {
                continue;
            }

            $type = $field->getType();
            if (!$type instanceof SchemaType) {
                continue;
            }

            $data[$fragmentName]['schemaVersion'] = $type->getSchemaVersion()->getVersion();
        }
    }
}
