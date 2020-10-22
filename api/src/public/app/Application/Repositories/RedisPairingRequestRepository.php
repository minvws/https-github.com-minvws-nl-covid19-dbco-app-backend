<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DateTime;
use DBCO\PublicAPI\Application\Models\PairingCase;
use Predis\Client as PredisClient;

/**
 * Used to complete pairing requests using Redis.
 *
 * @package DBCO\PrivateAPI\Application\Repositories
 */
class RedisPairingRequestRepository implements PairingRequestRepository
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
    public function completePairingRequest(string $code): ?PairingCase
    {
        $key = 'pairing-request:' . $code;
        list($rawPairingRequest, $del) = $this->client->transaction()->get($key)->del($key)->execute();

        // check if we successfully retrieved and deleted the pairing request
        if ($rawPairingRequest === null || $del !== 1) {
            return null;
        }

        $decodedPairingRequest = json_decode($rawPairingRequest);
        $decodedCase = $decodedPairingRequest->case;

        return new PairingCase($decodedCase->id, new DateTime($decodedCase->expiresAt));
    }
}
