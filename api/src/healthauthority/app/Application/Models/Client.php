<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Client.
 */
class Client
{
    /**
     * @var string
     */
    public string $token;

    /**
     * @var ClientCase
     */
    public ClientCase $case;

    /**
     * @var string
     */
    public string $clientPublicKey;

    /**
     * @var string
     */
    public string $healthAuthorityPublicKey;

    /**
     * @var string
     */
    public string $healthAuthoritySecretKey;

    /**
     * @var string
     */
    public string $sealedHealthAuthorityPublicKey;

    /**
     * @var string
     */
    public string $receiveKey;

    /**
     * @var string
     */
    public string $transmitKey;

    /**
     * Constructor.
     *
     * @param string     $token
     * @param ClientCase $case
     * @param string     $clientPublicKey
     * @param string     $healthAuthorityPublicKey
     * @param string     $healthAuthoritySecretKey
     * @param string     $sealedHealthAuthorityPublicKey
     * @param string     $receiveKey
     * @param string     $transmitKey
     */
    public function __construct(
        string $token,
        ClientCase $case,
        string $clientPublicKey,
        string $healthAuthorityPublicKey,
        string $healthAuthoritySecretKey,
        string $sealedHealthAuthorityPublicKey,
        string $receiveKey,
        string $transmitKey
    )
    {
        $this->token = $token;
        $this->case = $case;
        $this->clientPublicKey = $clientPublicKey;
        $this->healthAuthorityPublicKey = $healthAuthorityPublicKey;
        $this->healthAuthoritySecretKey = $healthAuthoritySecretKey;
        $this->sealedHealthAuthorityPublicKey = $sealedHealthAuthorityPublicKey;
        $this->receiveKey = $receiveKey;
        $this->transmitKey = $transmitKey;
    }
}
