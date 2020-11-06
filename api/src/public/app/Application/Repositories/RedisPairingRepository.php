<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DateTime;
use DBCO\PublicAPI\Application\Models\Pairing;
use Exception;
use Predis\Client as PredisClient;
use RuntimeException;

/**
 * Store/retrieve pairings in/from Redis.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class RedisPairingRepository implements PairingRepository
{
    const PAIRING_LIST_KEY = 'pairings';
    const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'pairing-response:%s';
    const PAIRING_TIMEOUT = 30;

    /**
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Constructor.
     *
     * @param PredisClient $client
     */
    public function __construct(PredisClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function storePairing(Pairing $pairing)
    {
        $responseListKey = sprintf(self::PAIRING_RESPONSE_LIST_KEY_TEMPLATE, $pairing->case->id);

        $data = [
            'pairing' => [
                'case' => [
                    'id' => $pairing->case->id,
                    'expiresAt' =>  $pairing->case->expiresAt->format(DateTime::ATOM)
                ],
                'encryptedClientPublicKey' => $pairing->encryptedClientPublicKey
            ],
            'responseListKey' => $responseListKey
        ];

        try {
            $this->client->rpush(self::PAIRING_LIST_KEY, json_encode($data));
            $response = $this->client->blpop($responseListKey, self::PAIRING_TIMEOUT);

            if ($response === null || count($response) !== 2) {
                throw new Exception('Error storing pairing (no response');
            }

            $responseData = json_decode($response[1]);
            $pairing->sealedHealthAuthorityPublicKey =
                base64_decode($responseData->pairing->sealedHealthAuthorityPublicKey);
        } catch (Exception $e) {
            throw new Exception('Error storing pairing', 0, $e);
        }
    }
}
