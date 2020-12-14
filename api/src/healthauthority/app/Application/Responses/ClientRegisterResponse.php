<?php
namespace DBCO\HealthAuthorityAPI\Application\Responses;

use DBCO\HealthAuthorityAPI\Application\Models\ClientRegistration;
use DBCO\Shared\Application\Responses\Response;
use JsonSerializable;

/**
 * Case response.
 */
class ClientRegisterResponse extends Response implements JsonSerializable
{
    /**
     * @var ClientRegistration
     */
    private ClientRegistration $registration;

    /**
     * Constructor.
     *
     * @param ClientRegistration $registration
     */
    public function __construct(ClientRegistration $registration)
    {
       $this->registration = $registration;
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
                base64_encode($this->registration->sealedHealthAuthorityPublicKey)
        ];
    }
}
