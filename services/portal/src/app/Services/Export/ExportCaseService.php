<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\Cursor;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Repositories\DbCaseRepository;
use App\Schema\SchemaObject;
use App\Services\Export\Helpers\ExportAuthorizationHelper;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportEncodingHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Illuminate\Support\Collection;

use function assert;

/**
 * @extends AbstractExportService<EloquentCase>
 */
class ExportCaseService extends AbstractExportService
{
    private const EXPORT_TYPE = ExportType::Case_;
    private const OBJECT_ROUTE_NAME = 'api-export-case';

    public function __construct(
        ExportPseudoIdHelper $pseudoIdHelper,
        ExportCursorHelper $cursorHelper,
        ExportEncodingHelper $encodingHelper,
        private readonly DbCaseRepository $caseRepository,
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
        return $this->caseRepository->getCaseForExport($id);
    }

    protected function validateAccessForClient(SchemaObject $object, ExportClient $client): void
    {
        assert($object instanceof EloquentCase);

        $this->authorizationHelper->validateOrganisationAccessForClient($object->organisation, $client);
    }

    protected function getMutationsForClient(Cursor $cursor, int $limit, ExportClient $client): Collection
    {
        $organisationIds = $client->organisations->map(static fn (EloquentOrganisation $o) => $o->uuid);
        return $this->caseRepository->getMutatedCasesForOrganisations($organisationIds, $cursor, $limit);
    }
}
