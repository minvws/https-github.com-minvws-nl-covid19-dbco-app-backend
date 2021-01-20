<?php
namespace DBCO\PublicAPI\Application\Responses;

/**
 * General task list response.
 */
class GeneralTaskListResponse extends ProxyResponse
{
    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        $headers = parent::getHeaders();
        $headers['Content-Type'] = 'application/json';
        return $headers;
    }
}
