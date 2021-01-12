<?php
namespace DBCO\Bridge\Application\Models;

use DateTimeInterface;

/**
 * Represents a request from the queue.
 */
class Request
{
    /**
     * Params key/value array (string -> string).
     *
     * @var array
     */
    public array $params;

    /**
     * Data.
     *
     * @var string
     */
    public string $data;

    /**
     * Client trace ID.
     *
     * @var string|null
     */
    public ?string $originTraceId;

    /**
     * Client sent timestamp.
     *
     * @var DateTimeInterface|null
     */
    public ?DateTimeInterface $originSentAt;

    /**
     * Constructor.
     *
     * @param array                  $params
     * @param string                 $data
     * @param string|null            $originTraceId
     * @param DateTimeInterface|null $originSentAt
     */
    public function __construct(array $params, string $data, ?string $originTraceId, ?DateTimeInterface $originSentAt)
    {
        $this->params = $params;
        $this->data = $data;
        $this->originTraceId = $originTraceId;
        $this->originSentAt = $originSentAt;
    }
}
