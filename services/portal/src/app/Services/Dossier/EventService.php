<?php

declare(strict_types=1);

namespace App\Services\Dossier;

use App\Models\Disease\Disease;
use App\Models\Dossier\Dossier;
use App\Models\Dossier\Event;
use App\Repositories\Dossier\EventRepository;
use App\Schema\Validation\ValidationResult;
use App\Services\Disease\DiseaseSchemaService;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\EncodingContext;

use function array_replace_recursive;
use function assert;
use function filter_var;
use function is_array;
use function is_int;
use function is_object;

use const FILTER_VALIDATE_INT;

class EventService
{
    public function __construct(private readonly EventRepository $eventRepository, private readonly DiseaseSchemaService $diseaseSchemaService)
    {
    }

    public function getEvent(string|int $id): ?Event
    {
        $filteredId = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($filteredId)) {
            return null;
        }

        return $this->eventRepository->getEvent($filteredId);
    }

    public function makeEvent(Dossier $dossier): Event
    {
        return $this->eventRepository->makeEvent($dossier);
    }

    public function validateEvent(Dossier $dossier, ?Event $event, ?array $data): ValidationResult
    {
        $schemaVersion = $event?->getSchemaVersion() ?? $this->diseaseSchemaService->getEventSchema(
            $dossier->diseaseModel,
        )->getCurrentVersion();

        $allData = [];

        if ($event?->exists) {
            $encoder = new Encoder();
            $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
            $encoder->getContext()->setUseAssociativeArraysForObjects(true);
            $encoder->getContext()->registerDecorator(Dossier::class, $schemaVersion->getEncodableDecorator());
            $allData = $encoder->encode($event);
            assert(is_array($allData));
        }

        $allData = $data !== null ? array_replace_recursive($allData, $data) : $allData;

        return $schemaVersion->validate($allData);
    }

    public function encodeEvent(Event $event): object
    {
        $encoder = new Encoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_OUTPUT);
        $encoder->getContext()->registerDecorator(Disease::class, $event->getSchemaVersion()->getEncodableDecorator());
        $data = $encoder->encode($event);
        assert(is_object($data));
        return $data;
    }

    public function decodeEvent(Event $event, array $data): Event
    {
        $decoder = new Decoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
        $decoder->getContext()->registerDecorator(Event::class, $event->getSchemaVersion()->getDecodableDecorator());
        $decoder->decode($data)->decodeObject(Event::class, $event);
        return $event;
    }

    public function saveEvent(Event $event): void
    {
        $this->eventRepository->saveEvent($event);
    }
}
