<?php
namespace MinVWS\Metrics\Repositories;

use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;

/**
 * Responsible for exporting to a file.
 *
 * @package MinVWS\Metrics\Repositories
 */
interface ExportRepository
{
    /**
     * Open export file.
     *
     * @param string $path
     * @param Export $export
     *
     * @return resource
     */
    public function openFile(string $path, Export $export);

    /**
     * Add event to resource.
     *
     * @param Event    $event
     * @param resource $handle
     *
     * @return mixed
     */
    public function addEventToFile(Event $event, $handle);

    /**
     * Close export file.
     *
     * @param resource $handle
     *
     * @return mixed
     */
    public function closeFile($handle);
}