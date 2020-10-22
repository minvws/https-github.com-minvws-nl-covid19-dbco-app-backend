<?php
namespace DBCO\PublicAPI\Application\Responses;

use DBCO\Shared\Application\Responses\Response;

/**
 * Response for case task submit.
 */
class CaseSubmitResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 204;
    }
}
