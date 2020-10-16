<?php
namespace App\Application\Models;

/**
 * Used for forwarding a body and headers 1:1 from the middleware.
 */
class ProxyData
{
    /**
     * @var Header[]
     */
    public array $headers;

    /**
     * Body. Should be a valid JSON string.
     *
     * @var string
     */
    public string $body;

    /**
     * Constructor.
     *
     * @param array  $headers
     * @param string $body
     */
    public function __construct(array $headers, string $body)
    {
        $this->headers = $headers;
        $this->body = $body;
    }
}
