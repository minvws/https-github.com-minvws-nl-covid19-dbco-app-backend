<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateContextRequest;
use App\Http\Requests\UpdateContextRequest;
use App\Http\Responses\Context\ContextEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\ContextSection;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use App\Repositories\SectionRepository;
use App\Services\ContextService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

use function array_key_exists;
use function response;

class ApiContextController extends ApiController
{
    public function __construct(
        private readonly ContextService $contextService,
        private readonly SectionRepository $sectionRepository,
    ) {
    }

    #[SetAuditEventDescription('Case contexts opgehaald')]
    public function getCaseContexts(
        EloquentCase $eloquentCase,
        Request $request,
        ContextEncoder $contextEncoder,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        // Optional parameters
        $group = $request->validate([
            'group' => 'nullable|in:source,contagious',
        ])['group'] ?? null;

        $contexts = $this->contextService->getContextsForCase($eloquentCase, $group);

        $auditEvent->objects(
            AuditObject::createArray(
                $contexts->all(),
                static function (Context $context) {
                    return AuditObject::create('context', $context->uuid);
                },
            ),
        );

        return EncodableResponseBuilder::create(['contexts' => $contexts])
            ->withContext(static function (EncodingContext $context) use ($contextEncoder): void {
                $context->registerDecorator(Context::class, $contextEncoder);
            })
            ->build();
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case context bijgewerkt')]
    public function updateContext(
        UpdateContextRequest $request,
        Context $context,
        ContextEncoder $contextEncoder,
    ): EncodableResponse {
        $request->setCase($context->case);

        $data = $request->validated()['context'];

        $context = $this->contextService->updateContext(
            $context,
            $data['label'] ?? null,
            $data['placeUuid'] ?? null,
            ContextRelationship::tryFromOptional($data['relationship'] ?? null),
            $data['otherRelationship'] ?? null,
            $data['explanation'] ?? null,
            $data['detailedExplanation'] ?? null,
            $data['remarks'] ?? null,
            $data['isSource'] ?? false,
            $data['moments'] ?? [],
        );

        $context->refresh();

        return EncodableResponseBuilder::create(['context' => $context])
            ->withContext(static function (EncodingContext $context) use ($contextEncoder): void {
                $context->registerDecorator(Context::class, $contextEncoder);
            })
            ->build();
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case context aangemaakt')]
    public function createContext(
        CreateContextRequest $request,
        EloquentCase $eloquentCase,
        ContextEncoder $contextEncoder,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        $auditEvent->object(AuditObject::create('context', $eloquentCase->uuid));

        $request->setCase($eloquentCase);

        $data = $request->validated()['context'];

        $context = $this->contextService->createContext(
            $data['label'] ?? null,
            $data['placeUuid'] ?? null,
            ContextRelationship::fromOptional($data['relationship'] ?? null),
            $data['otherRelationship'] ?? null,
            $data['explanation'] ?? null,
            $data['detailedExplanation'] ?? null,
            $data['remarks'] ?? null,
            array_key_exists('isSource', $data) ? (bool) $data['isSource'] : false,
            $data['moments'] ?? [],
            $eloquentCase,
        );

        return EncodableResponseBuilder::create(['context' => $context])
            ->withContext(static function (EncodingContext $context) use ($contextEncoder): void {
                $context->registerDecorator(Context::class, $contextEncoder);
            })
            ->build();
    }

    #[SetAuditEventDescription('Case context verwijderd')]
    public function deleteContext(Context $context): JsonResponse
    {
        try {
            $context->delete();
            return response()->json(['context' => null]);
        } catch (Throwable $t) {
            return response()->json(
                ['error' => 'Kan context niet verwijderen'],
                Response::HTTP_FORBIDDEN,
            );
        }
    }

    #[SetAuditEventDescription('Case context sectie opgeslagen')]
    public function storeContextSection(
        Context $context,
        Section $section,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $auditEvent->object(AuditObject::create('context-section', $context->uuid . '-' . $section->uuid));

        $contextSection = new ContextSection();
        $contextSection->context_uuid = $context->uuid;
        $contextSection->section_uuid = $section->uuid;
        $contextSection->save();

        // insert context, section into link table context_section
        return response()->json([], Response::HTTP_CREATED);
    }

    #[SetAuditEventDescription('Case context sectie verwijderd')]
    public function deleteContextSection(
        Context $context,
        Section $section,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $auditEvent->object(AuditObject::create('context-section', $context->uuid . '-' . $section->uuid));

        $this->sectionRepository->unlinkContextFromSection($context, $section);

        // delete context, section from link table context_section
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    #[SetAuditEventDescription('Case context sectie opgehaald')]
    public function getContextSections(Context $context): JsonResponse
    {
        $sections = $context->sections->map(static function (Section $section): array {
            return [
                'label' => $section->label,
                'uuid' => $section->uuid,
                'indexCount' => $section->indexCount(),
            ];
        });

        return response()->json([
            'sections' => $sections,
        ]);
    }

    #[SetAuditEventDescription('Plaats aan context gelinkt')]
    public function linkPlaceToContext(
        Context $context,
        Place $place,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $auditEvent->object(AuditObject::create('context-place', $context->uuid . '-' . $place->uuid));

        $this->contextService->linkPlaceToContext($context, $place);

        return response()->json([], Response::HTTP_CREATED);
    }

    #[SetAuditEventDescription('Plaats van context verwijderd')]
    public function unlinkPlaceFromContext(
        Context $context,
        Place $place,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $auditEvent->object(AuditObject::create('context-place', $context->uuid . '-' . $place->uuid));

        try {
            $this->contextService->unlinkPlaceFromContext($context, $place);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
