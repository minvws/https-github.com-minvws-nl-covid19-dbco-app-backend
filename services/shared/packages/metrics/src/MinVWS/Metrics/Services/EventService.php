<?php
namespace MinVWS\Metrics\Services;

use DateTimeImmutable;
use MinVWS\Metrics\Events\Event;
use MinVWS\Metrics\Models\Event as EventModel;
use MinVWS\Metrics\Repositories\StorageRepository;
use MinVWS\Metrics\Transformers\EventTransformer;
use Ramsey\Uuid\Uuid;

/**
 * Event service.
 *
 * @package MinVWS\Metrics\Services
 */
class EventService
{
    /**
     * @var StorageRepository
     */
    private StorageRepository $storageRepository;

    /**
     * @var EventTransformer
     */
    private EventTransformer $eventTransformer;

    /**
     * Constructor.
     *
     * @param StorageRepository $storageRepository
     * @param EventTransformer  $eventTransformer
     */
    public function __construct(
        StorageRepository $storageRepository,
        EventTransformer $eventTransformer
    ) {
        $this->storageRepository = $storageRepository;
        $this->eventTransformer = $eventTransformer;
    }

    /**
     * Register event.
     *
     * @param Event $event
     *
     * @return EventModel
     */
    public function registerEvent(Event $event): EventModel
    {
        $eventModel = new EventModel(Uuid::uuid4(), $event->getType(), $event->getData(), [], new DateTimeImmutable());
        $eventModel->exportData = $this->eventTransformer->exportDataForEvent($eventModel);
        $this->storageRepository->createEvent($eventModel);
        return $eventModel;
    }
}
