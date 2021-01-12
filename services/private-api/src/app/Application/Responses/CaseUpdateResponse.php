<?php
namespace DBCO\PrivateAPI\Application\Responses;

use DBCO\Shared\Application\Responses\Response;

/**
 * Response for the update case action.
 */
class CaseUpdateResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 204;
    }
}
