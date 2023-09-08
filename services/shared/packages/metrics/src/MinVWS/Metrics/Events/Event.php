<?php

namespace MinVWS\Metrics\Events;

/**
 * Metrics event interface.
 */
interface Event
{
    /**
     * Returns the event type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns the event data.
     *
     * @return array
     */
    public function getData(): array;
}
