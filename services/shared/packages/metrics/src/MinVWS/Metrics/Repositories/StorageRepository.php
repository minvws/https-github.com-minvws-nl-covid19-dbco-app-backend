<?php
namespace MinVWS\Metrics\Repositories;

use Closure;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;

/**
 * Store events and exports.
 *
 * @package MinVWS\Metrics\Repositories
 */
interface StorageRepository
{
    /**
     * Store event.
     *
     * @param Event $event
     *
     * @return void
     */
    public function createEvent(Event $event): void;

    /**
     * Count exports with the given status.
     *
     * @param array $status
     *
     * @return int
     */
    public function countExports(array $status): int;

    /**
     * List exports with the given status.
     *
     * @param int        $limit
     * @param int        $offset
     * @param array|null $status
     *
     * @return array
     */
    public function listExports(int $limit, int $offset, ?array $status): array;

    /**
     * Retrieve export.
     *
     * @param string $exportUuid
     *
     * @return Export|null
     */
    public function getExport(string $exportUuid): ?Export;

    /**
     * Store export.
     *
     * Also adds all events that have not been added to an export to the export.
     *
     * @param Export $export
     *
     * @return void
     */
    public function createExport(Export $export): void;

    /**
     * Iterate all events that are part of the given export.
     *
     * @param string  $exportUuid
     * @param Closure $callback
     *
     * @return mixed
     */
    public function iterateEventsForExport(string $exportUuid, Closure $callback): void;

    /**
     * Update export.
     *
     * @param Export   $export
     * @param string[] $fields
     */
    public function updateExport(Export $export, array $fields): void;
}