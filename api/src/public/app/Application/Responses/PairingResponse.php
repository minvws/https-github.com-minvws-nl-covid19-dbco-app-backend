<?php
namespace App\Application\Responses;

use DBCO\Application\Models\Pairing;
use DBCO\Application\Responses\Response;

/**
 * Response for pairing completion.
 */
class PairingResponse extends Response
{
    /**
     * @var Pairing
     */
    private Pairing $pairing;

    /**
     * Constructor.
     *
     * @param Pairing $pairing Pairing.
     */
    public function __construct(Pairing $pairing)
    {
        $this->pairing = $pairing;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'case' => [
                'id' =>
                    $this->pairing->case->id,
                'expiresAt' =>
                    $this->pairing->case->expiresAt
                        ->setTimezone(new \DateTimeZone('UTC'))
                        ->format('Y-m-d\TH:i:s\Z')
            ],
            'signingKey' => $this->pairing->signingKey
        ];
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return 201;
    }
}
