<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\PlaceVerificationException;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Place\VerifyMultipleRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\Place\PlaceEncoder;
use App\Models\Eloquent\Place;
use App\Services\PlaceAdminService;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use function response;

class ApiPlaceVerificationController extends Controller
{
    use ValidatesModels;

    public function __construct(
        private readonly PlaceAdminService $placeAdminService,
    ) {
    }

    #[SetAuditEventDescription('Plaats geverifieerd')]
    public function verifyPlace(Place $place, PlaceEncoder $placeEncoder): EncodableResponse
    {
        try {
            $this->placeAdminService->verifyPlace($place);
        } catch (PlaceVerificationException $e) {
            return new EncodableResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return
            EncodableResponseBuilder::create($place)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                    $context->registerDecorator(Place::class, $placeEncoder);
            })
                ->build();
    }

    #[SetAuditEventDescription('Multi plaats geverifieerd')]
    public function verifyPlaceMulti(VerifyMultipleRequest $request, AuditEvent $auditEvent): Response
    {
        $placeIds = $request->getPlaceUuids();

        try {
            $this->placeAdminService->verifyPlaces($placeIds);
            foreach ($placeIds as $placeId) {
                $auditEvent->object(AuditObject::create("place", $placeId));
            }
        } catch (PlaceVerificationException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return response(null, Response::HTTP_OK);
    }

    #[SetAuditEventDescription('Plaats ongeverifieerd')]
    public function unVerifyPlace(Place $place, PlaceEncoder $placeEncoder): EncodableResponse
    {
        try {
            $this->placeAdminService->unVerifyPlace($place);
        } catch (PlaceVerificationException $e) {
            return new EncodableResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return
            EncodableResponseBuilder::create($place)
            ->withContext(static function (EncodingContext $context) use ($placeEncoder): void {
                    $context->registerDecorator(Place::class, $placeEncoder);
            })
                ->build();
    }
}
