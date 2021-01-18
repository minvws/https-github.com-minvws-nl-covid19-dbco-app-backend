<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Responses;

use HealthCheckResultList;
use JsonSerializable;


/**
 * Health check response.
 *
 * @package DBCO\Shared\Application\Responses
 */
class HealthCheckResponse extends Response implements JsonSerializable
{
    /**
     * @var HealthCheckResultList
     */
    private HealthCheckResultList $result;

    /**
     * Constructor.
     *
     * @param HealthCheckResultList $result
     */
    public function __construct(HealthCheckResultList $result)
    {
        $this->result = $result;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->result->isHealthy ? 200 : 503;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $data = [
            'isHealthy' => $this->isHealthy,
            'results' => []
        ];

        foreach ($this->result->results as $label => $result) {
            $data['results'][$label] = ['isHealthy' => $result->isHealthy];
            if (!$result->isHealthy && isset($result->errorCode) && isset($result->errorMessage)) {
                $data['results'][$label]['errorCode'] = $result->errorCode;
                $data['results'][$label]['errorMessage'] = $result->errorMessage;
            }
        }

        return $data;
    }
}
