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
        $key = 'case:' . $pairing->case->id . ':pairing';
        $expiresInSeconds = $pairing->case->expiresAt->getTimestamp() - time();
        $data = [
            'case' => [
                'id' => $pairing->case->id,
                'expiresAt' =>  $pairing->case->expiresAt->format(DateTime::ATOM)
            ],
            'signingKey' => $pairing->signingKey
        ];

        try {
            $this->client->hset($key, $expiresInSeconds, json_encode($data));
        } catch (Exception $e) {
            throw new RuntimeException('Error storing pairing', 0, $e);
        }
    }
}
