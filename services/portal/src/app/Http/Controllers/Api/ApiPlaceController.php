<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\LocationApiUnauthenticatedException;
use App\Helpers\PostalCodeHelper;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiRequest;
use App\Http\Requests\Api\Place\Cases\ListRequest;
use App\Http\Requests\Api\Place\CreateRequest as CreatePlaceRequest;
use App\Http\Requests\Api\Place\MergeRequest as MergePlaceRequest;
use App\Http\Requests\Api\Place\SearchRequest as SearchPlaceRequest;
use App\Http\Requests\Api\Place\Sections\MergeRequest as MergeSectionsRequest;
use App\Http\Requests\Api\Place\Sections\StoreRequest as StoreSectionsRequest;
use App\Http\Requests\Api\Place\UpdateRequest as UpdatePlaceRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\Place\PlaceEncoder;
use App\Http\Responses\PlannerCase\CovidCaseWithAssignmentTokenEncoder;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use App\Models\Place\AddressLookup;
use App\Models\Place\Cases\ListOptions as CaseListOptions;
use App\Models\Place\ListOptions;
use App\Services\ContextService;
use App\Services\Location\LocationService;
use App\Services\PlaceAdminService;
use App\Services\PlaceService;
use App\Services\SectionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use function array_map;
use function collect;
use function count;
use function implode;
use function response;
use function trim;

class ApiPlaceController extends Controller
{
    use ValidatesModels;

    public function __construct(
        private readonly PlaceService $placeService,
        private readonly PlaceAdminService $placeAdminService,
        private readonly SectionService $sectionService,
        private readonly ContextService $contextService,
        private readonly LocationService $locationService,
    ) {
    }

    #[SetAuditEventDescription('Plaats opgehaald')]
    public function getPlace(string $placeUuid, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->object(AuditObject::create('place', $placeUuid));

        return response()->json($this->placeService->getPlace($placeUuid));
    }

    /**
     * @throws LocationApiUnauthenticatedException
     */
    public function searchPlaces(
        ApiRequest $request,
        AuditEvent $auditEvent,
        PlaceEncoder $placeEncoder,
    ): EncodableResponse {
        // the search string, e.g. name, zipcode, street
        // The api call should search our places table for existing places that match the query.
        $queryParameter = $request->getStringOrNull('query');
        $query = $queryParameter !== null ? Str::of($queryParameter)->trim() : null;
        if ($query === null || $query->length() < 2) {
            return EncodableResponseBuilder::create([
                'places' => [],
                'suggestions' => [],
            ])->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                $context->registerDecorator(Place::class, $placeEncoder);
            })->build();
        }

        $data = $this->placeService->searchPlace($query->toString());

        $auditEvent->objects(
            AuditObject::createArray(
                $data['places'],
                static fn (Place $place) => AuditObject::create('place', $place->uuid)
            ),
        );

        return EncodableResponseBuilder::create($data)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                $context->registerDecorator(Place::class, $placeEncoder);
            })
            ->build();
    }

    /**
     * @throws ValidationException
     */
    #[SetAuditEventDescription('Adres opgehaald')]
    public function addressLookup(ApiRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $postalCode = PostalCodeHelper::normalize($request->getString('postalCode'));
        $houseNumber = trim($request->getString('houseNumber'));

        $auditEvent->object(
            AuditObject::create('address', "$postalCode-$houseNumber")
                ->detail('postalCode', $postalCode)
                ->detail('houseNumber', $houseNumber),
        );

        $addresses = $this->placeService->lookupAddress($postalCode, $houseNumber);

        $validationResult = $this->validateModel(AddressLookup::class, [
            'postalCode' => $postalCode,
            'houseNumber' => $houseNumber,
        ]);

        if ($addresses === null || count($addresses) === 0 || isset($validationResult[Validatable::SEVERITY_LEVEL_FATAL])) {
            return response()->json(
                [
                    'error' => 'Geen adres gevonden',
                    'validationResult' => $validationResult,
                ],
                Response::HTTP_NOT_FOUND,
            );
        }

        return response()->json([
            'address' => [
                'street' => $addresses[0]['street'],
                'town' => $addresses[0]['town'],
            ],
            'organisationUuid' => $addresses[0]['organisation_uuid'],
            'validationResult' => $validationResult,
        ]);
    }

    #[SetAuditEventDescription('Plaats aangemaakt')]
    public function createPlace(CreatePlaceRequest $request, PlaceEncoder $placeEncoder, AuditEvent $auditEvent): EncodableResponse
    {
        $auditObject = AuditObject::create('place');
        $auditEvent->object($auditObject);

        /** @var array $validated */
        $validated = $request->validated();
        $place = $this->placeService->createPlace($validated);

        $auditObject->identifier($place->uuid);

        return EncodableResponseBuilder::create($place, Response::HTTP_CREATED)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                $context->registerDecorator(Place::class, $placeEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Plaats aangemaakt')]
    public function updatePlace(UpdatePlaceRequest $request, Place $place, PlaceEncoder $placeEncoder, AuditEvent $auditEvent): EncodableResponse
    {
        $place = $this->placeService->updatePlace($place, $request->validated());
        $auditEvent->object(AuditObject::create('place', $place->uuid));

        return EncodableResponseBuilder::create($place)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                $context->registerDecorator(Place::class, $placeEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Toon dossiers met deze context')]
    public function getPlaceCases(
        Place $place,
        ListRequest $request,
        CovidCaseWithAssignmentTokenEncoder $encoder,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        /** @var CaseListOptions $options */
        $options = $request->getDecodingContainer()->decodeObject(CaseListOptions::class);
        $paginator = $this->placeService->getCases($place, $options);

        $auditEvent->objects(
            collect($paginator->items())
                ->map(static fn($c) => AuditObject::create('covidcase', $c->uuid))
                ->toArray(),
        );

        return EncodableResponseBuilder::create($paginator)
            ->withContext(static function (EncodingContext $context) use ($encoder): void {
                $context->registerDecorator(EloquentCase::class, $encoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Plaats secties opgehaald')]
    public function getPlaceSections(Place $place, AuditEvent $auditEvent): JsonResponse
    {
        $sections = $this->placeService->getSections($place, true)
            //@phpstan-ignore-next-line because phpstan is complaining about the missing key parameter, but it is not needed here.
            ->map(static function (Section $section): array {
                return [
                    'label' => $section->label,
                    'uuid' => $section->uuid,
                    'indexCount' => $section->indexCount(),
                ];
            });

        $auditEvent->objects(
            AuditObject::createArray(
                $sections->all(),
                static fn ($section) => AuditObject::create('section', $section['uuid']),
            ),
        );

        return response()->json(['sections' => $sections]);
    }

    #[SetAuditEventDescription('Plaats sectie opgeslagen')]
    public function createPlaceSection(Place $place, StoreSectionsRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditObject = AuditObject::create('place-section');
        $auditEvent->object($auditObject);

        $contextUuid = $request->getStringOrNull('context_uuid');
        $context = $contextUuid !== null ? $this->contextService->getContext($contextUuid) : null;

        $sectionList = $this->sectionService->createSectionsWithContext($request->getSections(), $context, $place);

        if ($sectionList) {
            $auditObject->identifier(implode(',', array_map(static fn($section) => $section['uuid'], $sectionList)));
        } else {
            $auditObject->identifier('null');
        }

        return response()->json(['sections' => $sectionList]);
    }

    #[SetAuditEventDescription('Plaats sectie opgeslagen')]
    public function updatePlaceSection(StoreSectionsRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditObject = AuditObject::create('place-section');
        $auditEvent->object($auditObject);

        $sectionList = $this->sectionService->updateSectionsWithContext($request->getSections());

        if ($sectionList) {
            $auditObject->identifier(implode(',', array_map(static fn($section) => $section['uuid'], $sectionList)));
        } else {
            $auditObject->identifier('null');
        }

        return response()->json(['sections' => $sectionList]);
    }

    #[SetAuditEventDescription('Plaats secties samengevoegd')]
    public function mergePlaceSection(Place $place, Section $section, MergeSectionsRequest $request, AuditEvent $auditEvent): JsonResponse
    {
        $auditEvent->object(AuditObject::create('place-section', $section->uuid));

        if ($section->place->isNot($place)) {
            throw new BadRequestHttpException('Section does not belong to place');
        }

        /** @var array<string> $mergeSectionUuids */
        $mergeSectionUuids = $request->validated(MergeSectionsRequest::MERGE_SECTIONS_LABEL);
        $section = $this->sectionService->mergeSectionsInSection($section, $mergeSectionUuids);

        return response()->json([
            'section' => [
                'uuid' => $section->uuid,
                'label' => $section->label,
                'indexCount' => $section->indexCount(),
            ],
        ]);
    }

    /**
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Vergelijkbare plaatsen gezocht')]
    public function searchSimilarPlaces(SearchPlaceRequest $request, AuditEvent $event, PlaceEncoder $placeEncoder): EncodableResponse
    {
        /** @var ListOptions $options */
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);
        $queryParameter = $request->getStringOrNull('query');
        $query = $queryParameter !== null ? Str::of($queryParameter)->trim()->toString() : '';

        /** @var LengthAwarePaginator $searchResult */
        $searchResult = $this->placeAdminService->searchSimilarPlaces($query, $options);

        $event->objects($searchResult->map(static fn($p): AuditObject => AuditObject::create('place', $p->uuid))->toArray());

        return EncodableResponseBuilder::create($searchResult)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                $context->registerDecorator(stdClass::class, $placeEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Plaatsen samengevoegd')]
    public function mergePlace(MergePlaceRequest $request, Place $place, AuditEvent $auditEvent): EncodableResponse
    {
        /** @var array<string> $mergePlaces */
        $mergePlaces = $request->validated()[MergePlaceRequest::MERGE_PLACES];
        $auditEvent->objectDetails('place', 'mergePlaces', $mergePlaces);
        $place = $this->placeAdminService->mergePlace($place, $mergePlaces);

        return new EncodableResponse($place);
    }

    public function resetCount(Place $place): JsonResponse
    {
        $this->placeService->resetCount($place, CarbonImmutable::now());

        return response()->json([
            'index_count_reset_at' => $place->index_count_reset_at,
        ]);
    }
}
