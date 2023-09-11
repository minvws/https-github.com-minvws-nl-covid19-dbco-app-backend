<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Intake\ListRequest;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\Intake\IntakeEncoder;
use App\Models\Eloquent\Intake;
use App\Models\Intake\ListOptions;
use App\Services\Intake\IntakeService;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;

use function array_map;

class ApiIntakeController extends ApiController
{
    private IntakeService $intakeService;

    public function __construct(IntakeService $intakeService)
    {
        $this->intakeService = $intakeService;
    }

    /**
     * List intakes
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    #[SetAuditEventDescription('Intakes opgehaald')]
    public function listIntakes(ListRequest $request, AuditEvent $event, IntakeEncoder $intakeEncoder): EncodableResponse
    {
        $options = $request->getDecodingContainer()->decodeObject(ListOptions::class);
        $list = $this->intakeService->listIntakes($options);

        $event->objects(array_map(static fn ($i) => AuditObject::create('intakes', $i->uuid), $list->items()));

        return
            EncodableResponseBuilder::create($list)
            ->withContext(static function (EncodingContext $context) use ($intakeEncoder): void {
                    $context->registerDecorator(Intake::class, $intakeEncoder);
            })
                ->build();
    }
}
