<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaseList\CreateRequest;
use App\Http\Requests\Api\CaseList\DeleteRequest;
use App\Http\Requests\Api\CaseList\GetRequest;
use App\Http\Requests\Api\CaseList\ListRequest;
use App\Http\Requests\Api\CaseList\UpdateRequest;
use App\Http\Responses\EncodableResponse;
use App\Models\CaseList\ListOptions;
use App\Models\Eloquent\CaseList;
use App\Services\CaseListService;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;

use function abort_if;
use function response;

class ApiCaseListController extends ApiController
{
    public function __construct(
        private readonly CaseListService $caseListService,
    ) {
    }

    /**
     * List case lists
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Case lijsten opgehaald')]
    public function listCaseLists(ListRequest $request): EncodableResponse
    {
        /** @var ListOptions $options */
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);
        $paginator = $this->caseListService->listCaseLists($options);

        return new EncodableResponse($paginator, 200);
    }

    /**
     * Get case list
     */
    #[SetAuditEventDescription('Case lijst opgehaald')]
    public function getCaseList(string $caseListUuid, GetRequest $request): EncodableResponse
    {
        $caseList = $this->caseListService->getCaseListByUuid($caseListUuid, $request->stats);
        abort_if($caseList === null, 404);

        return new EncodableResponse($caseList, 200);
    }

    /**
     * Create case list
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Case lijst aangemaakt')]
    public function createCaseList(CreateRequest $request, AuditEvent $auditEvent): EncodableResponse
    {
        $container = $request->getDecodingContainer();

        /** @var CaseList $caseList */
        $caseList = $container->decodeObject(CaseList::class);
        $this->caseListService->createCaseList($caseList);

        $auditEvent->object(AuditObject::create('caselist', $caseList->uuid));

        return new EncodableResponse($caseList, 201);
    }

    /**
     * Update case list
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Case lijst bijgewerkt')]
    public function updateCaseList(CaseList $caseList, UpdateRequest $request): EncodableResponse
    {
        $container = $request->getDecodingContainer();
        $container->decodeObject(CaseList::class, $caseList);

        $this->caseListService->updateCaseList($caseList);

        return new EncodableResponse($caseList, 200);
    }

    /**
     * Delete case list
     */
    #[SetAuditEventDescription('Case lijst verwijderd')]
    public function deleteCaseList(CaseList $caseList, DeleteRequest $request): Response
    {
        $this->caseListService->deleteCaseList($caseList);

        return response('', 204);
    }
}
