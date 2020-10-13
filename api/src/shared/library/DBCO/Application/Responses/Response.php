<?php
declare(strict_types=1);

namespace DBCO\Application\Responses;

use JsonSerializable;
use RuntimeException;
use Slim\Psr7\Response as SlimResponse;


/**
 * Simple response builder.
 *
 * @package DBCO\Application\Responses
 */
abstract class Response
{
    /**
     * Returns the HTTP headers for this response.
     *
     * The default implementation returns a "Content-Type" header of
     * "application/json" if the subclass implements JsonSerializable.
     *
     * The structure of the array should be:
     * [
     *    "name1" => "value1",
     *    "name2" => ["value2", "value3"],
     *    ...
     * ]
     *
     * @return array
     */
    public function getHeaders(): array
    {
        if ($this instanceof JsonSerializable) {
            return ['Content-Type' => 'application/json'];
        } else {
            return [];
        }
    }

    /**
     * Returns the body.
     *
     * This method offers a default implementation for classes that implement JsonSerializable or
     * return a status code of 202 or 204. In all other cases this method should be implemented.
     *
     * @return string
     */
    public function getBody(): string
    {
        if ($this instanceof JsonSerializable) {
            return json_encode($this, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        } else if ($this->getStatusCode() === 202 || $this->getStatusCode() === 204) {
            return '';
        } else {
            throw new RuntimeException('Not implemented!');
        }
    }

    /**
     * Returns the status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return 200;
    }

    /**
     * Build response object.
     *
     * @param SlimResponse $response
     *
     * @return SlimResponse
     */
    public function respond(SlimResponse $response): SlimResponse
    {
        $response = $response->withStatus($this->getStatusCode());

        $response->getBody()->write($this->getBody());

        foreach ($this->getHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
