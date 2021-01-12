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