<?php

namespace MinVWS\Metrics\Transformers;

use MinVWS\Metrics\Models\Event;

/**
 * Responsible for transforming event data for export.
 *
 * @package MinVWS\Metrics\Transformers
 */
interface EventTransformer
{
    /**
     * Transform event data for export.
     *
     * @param Event $event
     *
     * @return array Export data (key/value).
     */
    public function exportDataForEvent(Event $event): array;
}
