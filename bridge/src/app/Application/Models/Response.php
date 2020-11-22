<?php
namespace DBCO\Bridge\Application\Models;

/**
 * Represents a response.
 */
class Response
{
    const SUCCESS = 'SUCCESS';
    const ERROR   = 'ERROR';

    /**
     * Response status.
     *
     * @var string
     */
    public string $status;

    /**
     * Response data.
     *
     * @var string
     */
    public string $data;

    /**
     * Constructor.
     *
     * @param string $status
     * @param string $data
     */
    public function __construct(string $status, string $data)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
