<?php
namespace DBCO\PrivateAPI\Application\Repositories;

use DateTime;
use DBCO\PrivateAPI\Application\Models\PairingRequest;
use Predis\Client as PredisClient;

/**
 * Store/retrieve pairing requests in/from Redis.
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
     * Returns the Redis key for the given pairing request code.
     *
     * @param string $code
     *
     * @return string
     */
    private function getRedisKeyForCode(string $code): string
    {
        return 'pairing-request:' . $code;
    }

    /**
     * @inheritDoc
     */
    public function isActivePairingCode(string $code): bool
    {
        $key = $this->getRedisKeyForCode($code);

        // key expires based on the pairing request expiration time, so
        // it won't be available anymore if the pairing has expired
        return $this->client->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function storePairingRequest(PairingRequest $request)
    {
        $key = $this->getRedisKeyForCode($request->code);
        $expiresInSeconds = $request->codeExpiresAt->getTimestamp() - time();
        $data = [
            'codeExpiresAt' => $request->codeExpiresAt->format(DateTime::ATOM),
            'case' => [
                'id' => $request->case->id,
                'expiresAt' => $request->case->expiresAt->format(DateTime::ATOM)
            ]
        ];

        $this->client->setex($key, $expiresInSeconds, json_encode($data));
    }
}
