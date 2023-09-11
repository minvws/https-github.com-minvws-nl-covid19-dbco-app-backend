<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Export\Cursor;
use App\Models\Export\ExportClient;
use App\Services\Export\Exceptions\ExportAuthorizationException;
use App\Services\Export\Exceptions\ExportCursorException;
use App\Services\Export\Exceptions\ExportNotFoundException;
use DateTimeInterface;

interface ExportService
{
    /**
     * @throws ExportAuthorizationException
     * @throws ExportNotFoundException
     */
    public function exportForClient(string $id, ExportClient $client): object;

    public function listForClient(Cursor $cursor, ExportClient $client): array;

    /**
     * @throws ExportCursorException
     */
    public function decodeCursorForClient(string $cursorToken, ExportClient $client): Cursor;

    public function createCursor(?DateTimeInterface $since = null, ?DateTimeInterface $until = null): Cursor;
}
