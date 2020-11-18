<?php

namespace App\Repositories;

use App\Models\Pairing;
use DateTimeInterface;
use GuzzleHttp\Client as GuzzleClient;
use Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use Jenssegers\Date\Date;

/**
 * Used for registering a new case for pairing.
 *
 * @package App\Repositories
 */
class ApiPairingRepository implements PairingRepository
{
    const JWT_EXPIRATION_TIME = 300; // 5 minutes

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var string
     */
    private string $jwtSecret;

    /**
     * Constructor.
     *
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client, string $jwtSecret)
    {
        $this->client = $client;
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * Encode JWT for registering case.
     *
     * @param string $caseUuid
     *
     * @return string
     */
    private function encodeJWT(string $caseUuid): string
    {
        $payload = array(
            "iat" => time(),
            "exp" => time() + self::JWT_EXPIRATION_TIME,
            "http://ggdghor.nl/cid" => $caseUuid
        );

        return JWT::encode($payload, $this->jwtSecret);
    }


    /**
     * Fetch pairing code for the given case.
     *
     * @param string            $caseUuid  The case to pair.
     * @param DateTimeInterface $expiresAt When it is not possible anymore to submit data for this case.
     *
     * @return Pairing A pairing code for this case
     *
     * @throws GuzzleException
     */
    public function getPairing(string $caseUuid, DateTimeInterface $expiresAt): Pairing
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->encodeJWT($caseUuid)
            ],
            'json' => [
                'caseId' => $caseUuid,
                'caseExpiresAt' => $expiresAt->format('c')
            ]
        ];

        $response = $this->client->post('cases', $options);
        $data = json_decode($response->getBody()->getContents());

        $pairing = new Pairing();
        $pairing->code = $data->pairingCode;
        $pairing->expiresAt = new Date($data->pairingCodeExpiresAt);

        return $pairing;
    }
}
