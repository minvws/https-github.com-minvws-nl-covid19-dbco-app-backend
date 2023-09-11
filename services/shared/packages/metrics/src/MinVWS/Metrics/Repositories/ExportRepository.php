<?php

namespace MinVWS\Metrics\Repositories;

use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Models\Intake;

/**
 * Responsible for exporting to a file stream.
 *
 * @package MinVWS\Metrics\Repositories
 */
interface ExportRepository
{
    /**
     * Add Event header to stream (if any).
     *
     * @param Export   $export
     * @param resource $handle
     */
    public function addHeaderToStream(Export $export, $handle);

    /**
     * Add event to resource.
     *
     * @param Event    $event
     * @param resource $handle
     */
    public function addObjectToStream($object, $handle);

    /**
     * Add footer to stream (if any).
     *
     * @param Export   $export
     * @param resource $handle
     */
    public function addFooterToStream(Export $export, $handle);
}
