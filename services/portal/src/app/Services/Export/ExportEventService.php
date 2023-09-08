<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Event;
use App\Models\Export\Cursor;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Repositories\EventRepository;
use App\Schema\SchemaObject;
use App\Services\Export\Helpers\ExportAuthorizationHelper;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportEncodingHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Illuminate\Support\Collection;

use function assert;
use function is_null;

/**
 * @extends AbstractExportService<Event>
 */
class ExportEventService extends AbstractExportService
{
    private const EXPORT_TYPE = ExportType::Event;
    private const OBJECT_ROUTE_NAME = 'api-export-event';

    public function __construct(
        ExportPseudoIdHelper $pseudoIdHelper,
        ExportCursorHelper $cursorHelper,
        ExportEncodingHelper $encodingHelper,
        private readonly EventRepository $eventRepository,
        private readonly ExportAuthorizationHelper $authorizationHelper,
    ) {
        parent::__construct($pseudoIdHelper, $cursorHelper, $encodingHelper);
    }

    protected function getExportType(): ExportType
    {
        return self::EXPORT_TYPE;
    }

    protected function getObjectRouteName(): string
    {
        return self::OBJECT_ROUTE_NAME;
    }

    protected function getObjectForClient(string $id, ExportClient $client): ?SchemaObject
    {
        return $this->eventRepository->getByUuid($id);
    }

    protected function validateAccessForClient(SchemaObject $object, ExportClient $client): void
    {
        assert(!is_null($object->organisation));
        $this->authorizationHelper->validateOrganisationAccessForClient($object->organisation, $client);
    }

    protected function getMutationsForClient(Cursor $cursor, int $limit, ExportClient $client): Collection
    {
        $organisationIds = $client->organisations->map(static fn(EloquentOrganisation $o) => $o->uuid);
        return $this->eventRepository->getMutatedEventsForOrganisations($organisationIds, $cursor, $limit);
    }
}
