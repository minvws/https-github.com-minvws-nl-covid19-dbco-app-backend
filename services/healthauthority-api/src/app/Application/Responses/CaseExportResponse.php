<?php
namespace DBCO\HealthAuthorityAPI\Application\Responses;

use DBCO\Shared\Application\Responses\Response;

/**
 * Response for case export.
 */
class CaseExportResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 204;
    }
}
