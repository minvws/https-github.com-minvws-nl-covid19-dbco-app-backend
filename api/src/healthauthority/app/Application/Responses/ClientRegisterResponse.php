<?php
namespace DBCO\HealthAuthorityAPI\Application\Responses;

use DBCO\HealthAuthorityAPI\Application\Models\Client;
use DBCO\Shared\Application\Responses\Response;
use JsonSerializable;

/**
 * Case response.
 */
class ClientRegisterResponse extends Response implements JsonSerializable
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * Constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
       $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 201;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'sealedHealthAuthorityPublicKey' =>
                base64_encode($this->client->sealedHealthAuthorityPublicKey)
        ];
    }
}
