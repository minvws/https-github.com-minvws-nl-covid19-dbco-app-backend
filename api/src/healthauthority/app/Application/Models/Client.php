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
    public string $uuid;

    /**
     * @var string
     */
    public string $caseUuid;

    /**
     * @var string
     */
    public string $token;

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
     * @param string $uuid
     * @param string $caseUuid
     * @param string $token
     * @param string $receiveKey
     * @param string $transmitKey
     */
    public function __construct(
        string $uuid,
        string $caseUuid,
        string $token,
        string $receiveKey,
        string $transmitKey
    )
    {
        $this->uuid = $uuid;
        $this->token = $token;
        $this->caseUuid = $caseUuid;
        $this->receiveKey = $receiveKey;
        $this->transmitKey = $transmitKey;
    }
}
