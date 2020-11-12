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
    private const PAIRING_LIST_KEY = 'pairings';
    private const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'pairing-response:%s';
    private const PAIRING_TIMEOUT = 30;

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
            'case' => [
                'id' => $pairing->case->id,
            ],
            'sealedClientPublicKey' => $pairing->sealedClientPublicKey
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
