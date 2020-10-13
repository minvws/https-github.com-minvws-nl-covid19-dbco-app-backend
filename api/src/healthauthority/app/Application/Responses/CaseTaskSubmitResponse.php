<?php
namespace App\Application\Responses;

use DBCO\Application\Responses\Response;

/**
 * Response for case task submit.
 */
class CaseTaskSubmitResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 204;
    }
}
