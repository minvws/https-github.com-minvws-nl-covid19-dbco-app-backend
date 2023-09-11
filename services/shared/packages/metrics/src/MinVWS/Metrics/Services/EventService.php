<?php

namespace MinVWS\Metrics\Services;

use DateTimeImmutable;
use DateTimeZone;
use MinVWS\Metrics\Events\Event;
use MinVWS\Metrics\Models\Event as EventModel;
use MinVWS\Metrics\Repositories\StorageRepository;
use MinVWS\Metrics\Transformers\EventTransformer;
use Ramsey\Uuid\Uuid;

class EventService
{
    protected StorageRepository $storageRepository;
    private EventTransformer $eventTransformer;

    public function __construct(
        StorageRepository $storageRepository,
        EventTransformer $eventTransformer
    ) {
        $this->storageRepository = $storageRepository;
        $this->eventTransformer = $eventTransformer;
    }

    public function registerEvent(Event $event): EventModel
    {
        $eventModel = new EventModel(Uuid::uuid4(), $event->getType(), $event->getData(), [], new DateTimeImmutable('now', new DateTimeZone('UTC')));
        $eventModel->exportData = $this->eventTransformer->exportDataForEvent($eventModel);
        $this->storageRepository->createEvent($eventModel);
        return $eventModel;
    }
}
