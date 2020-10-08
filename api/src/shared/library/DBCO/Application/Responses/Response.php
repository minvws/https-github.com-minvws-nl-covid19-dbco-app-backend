<?php
declare(strict_types=1);

namespace DBCO\Application\Responses;

use JsonSerializable;

/**
 * Response.
 *
 * @package DBCO\Application\Responses
 */
abstract class Response implements JsonSerializable
{
    /**
     * Returns the status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return 200;
    }
}
