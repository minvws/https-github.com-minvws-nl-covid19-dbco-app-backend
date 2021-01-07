<?php
namespace DBCO\Bridge\Application\Destinations;

use DBCO\Bridge\Application\Models\Request;
use DBCO\Bridge\Application\Models\Response;
use Throwable;

/**
 * Pairing gateway for the client.
 *
 * @package App\Application\Repositories
 */
interface Destination
{
    /**
     * Send request and return the response.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws Throwable
     */
    public function sendRequest(Request $request): Response;
}
