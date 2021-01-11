<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Client registration.
 */
class ClientRegistration
{
    /**
     * @var Client
     */
    public Client $client;

    /**
     * @var string
     */
    public string $sealedHealthAuthorityPublicKey;

    /**
     * Constructor.
     */
    public function __construct(Client $client, string $sealedHealthAuthorityPublicKey)
    {
        $this->client = $client;
        $this->sealedHealthAuthorityPublicKey = $sealedHealthAuthorityPublicKey;
    }
}
