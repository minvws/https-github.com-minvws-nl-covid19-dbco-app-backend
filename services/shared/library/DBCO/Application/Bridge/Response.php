<?php
namespace DBCO\Shared\Application\Bridge;

/**
 * Bridge response.
 */
class Response
{
    /**
     * @var string
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $data Response data.
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * Get data payload.
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }
}