<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Callcenter;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Callcenter\SearchRequest;
use App\Http\Responses\Callcenter\SearchResultEncoder;
use App\Http\Responses\EncodableResponseBuilder;
use App\Services\SearchHash\SearchResult;
use App\Services\SearchHash\SearchService;
use App\Services\SearchHash\Slot\Slots;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpFoundation\Response;

class ApiCallcenterSearchController extends ApiController
{
    public function __construct(
        private readonly SearchService $searchService,
        protected SearchResultEncoder $searchResultEncoder,
    ) {
    }

    #[SetAuditEventDescription('Zoek dossier')]
    public function search(SearchRequest $request): Response
    {
        /** @var array{
         *    dateOfBirth:string,
         *    lastThreeBsnDigits?:string,
         *    lastname?:string,
         *    postalCode?:string,
         *    houseNumber?:string,
         *    houseNumberSuffix?:string,
         *    phone?:string
         * } $data */
        $data = $request->validated();

        $results = $this->searchService->search(new Slots(...$data));

        return
            EncodableResponseBuilder::create($results)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(SearchResult::class, $this->searchResultEncoder);
            })
            ->build();
    }
}
