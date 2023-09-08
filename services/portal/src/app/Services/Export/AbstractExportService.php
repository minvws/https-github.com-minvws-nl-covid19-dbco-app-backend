<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Export\Cursor;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Export\Mutation;
use App\Schema\SchemaObject;
use App\Services\Export\Exceptions\ExportAuthorizationException;
use App\Services\Export\Exceptions\ExportCursorException;
use App\Services\Export\Exceptions\ExportNotFoundException;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportEncodingHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;

use function assert;

/**
 * @template TObject of SchemaObject
 */
abstract class AbstractExportService implements ExportService
{
    public const PAGE_SIZE = 100;
    protected const DEFAULT_SINCE = '1 hour ago';

    public function __construct(
        protected readonly ExportPseudoIdHelper $pseudoIdHelper,
        protected readonly ExportCursorHelper $cursorHelper,
        protected readonly ExportEncodingHelper $encodingHelper,
    ) {
    }

    abstract protected function getExportType(): ExportType;

    abstract protected function getObjectRouteName(): string;

    /**
     * @return TObject|null
     */
    abstract protected function getObjectForClient(string $id, ExportClient $client): ?SchemaObject;

    /**
     * @param TObject $object
     *
     * @throws ExportAuthorizationException
     */
    abstract protected function validateAccessForClient(SchemaObject $object, ExportClient $client): void;

    /**
     * @return Collection<Mutation>
     */
    abstract protected function getMutationsForClient(Cursor $cursor, int $limit, ExportClient $client): Collection;

    /**
     * @throws ExportAuthorizationException
     * @throws ExportNotFoundException
     */
    public function exportForClient(string $id, ExportClient $client): object
    {
        $object = $this->getObjectForClient($id, $client);
        if ($object === null) {
            throw new ExportNotFoundException();
        }

        $this->validateAccessForClient($object, $client);
        return $this->encodingHelper->encodeForClient($object, $client);
    }

    public function listForClient(Cursor $cursor, ExportClient $client): array
    {
        $mutations = $this->getMutationsForClient($cursor, self::PAGE_SIZE, $client);

        if ($mutations->isNotEmpty()) {
            $lastMutation = $mutations->last();
            assert($lastMutation instanceof Mutation);
            $nextCursor = $this->cursorHelper->createNextPageCursor($cursor, $lastMutation);
        } else {
            $nextCursor = $cursor;
        }

        return $this->encodingHelper->encodeMutationsForClient(
            $mutations,
            $this->getObjectRouteName(),
            $this->cursorHelper->encodeCursorToTokenForClient($nextCursor, $this->getExportType(), $client),
            $client,
        );
    }

    /**
     * @throws ExportCursorException
     */
    public function decodeCursorForClient(string $cursorToken, ExportClient $client): Cursor
    {
        return $this->cursorHelper->decodeCursorFromTokenForClient($cursorToken, $this->getExportType(), $client);
    }

    public function createCursor(?DateTimeInterface $since = null, ?DateTimeInterface $until = null): Cursor
    {
        if ($since === null) {
            $since = new DateTimeImmutable(self::DEFAULT_SINCE);
        }

        return $this->cursorHelper->createFirstPageCursor($since, $until);
    }
}
