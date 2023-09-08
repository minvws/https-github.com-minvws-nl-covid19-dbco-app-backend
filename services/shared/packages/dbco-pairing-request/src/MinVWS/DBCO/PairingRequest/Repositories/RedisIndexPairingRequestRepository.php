<?php

namespace MinVWS\DBCO\PairingRequest\Repositories;

use MinVWS\Codable\JSONDecoder;
use MinVWS\DBCO\PairingRequest\Models\IndexPairingRequest;
use Predis\Client as PredisClient;

/**
 * Used to complete pairing requests using Redis.
 */
class RedisIndexPairingRequestRepository implements IndexPairingRequestRepository
{
    private const PAIRING_REQUEST_BLOCKED_CODE_KEY_TEMPLATE = 'index-pairing-request:blocked-code:%s';
    private const PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE      = 'index-pairing-request:code:%s';
    private const PAIRING_REQUEST_BY_TOKEN_KEY_TEMPLATE     = 'index-pairing-request:token:%s';

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
    public function isPairingRequestCodeAvailable(string $code): bool
    {
        $key = sprintf(self::PAIRING_REQUEST_BLOCKED_CODE_KEY_TEMPLATE, $code);

        // key expires automatically when the code becomes available again, so
        // if it still exists, we know it is not available
        return $this->client->exists($key) === 0;
    }

    /**
     * @inheritDoc
     */
    public function blockPairingRequestCode(string $code, int $ttl): bool
    {
        $key = sprintf(self::PAIRING_REQUEST_BLOCKED_CODE_KEY_TEMPLATE, $code);

        // set if not exists
        if ($this->client->setnx($key, $code) === 1) {
            $this->client->expire($key, $ttl); // set expiry
            return true;
        } else {
            return false;
        }
    }

    /**
     * Decode pairing request.
     *
     * @param string|null $json
     *
     * @return IndexPairingRequest|null
     */
    private function decodePairingRequest(?string $json): ?IndexPairingRequest
    {
        if ($json === null) {
            return null;
        }

        $decoder = new JSONDecoder();

        return
            $decoder
                ->decode($json)
                ->decodeObject(IndexPairingRequest::class);
    }

    /**
     * @inheritDoc
     */
    public function getPairingRequestByCode(string $code): ?IndexPairingRequest
    {
        $key = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $code);
        $json = $this->client->get($key);
        return $this->decodePairingRequest($json);
    }

    /**
     * @inheritDoc
     */
    public function getPairingRequestByToken(string $token): ?IndexPairingRequest
    {
        $key = sprintf(self::PAIRING_REQUEST_BY_TOKEN_KEY_TEMPLATE, $token);
        $json = $this->client->get($key);
        return $this->decodePairingRequest($json);
    }

    /**
     * @inheritDoc
     */
    public function storePairingRequest(IndexPairingRequest $request): void
    {
        $json = json_encode($request);

        // code lookup expires at warning time
        $byCodeKey = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $request->code);
        if ($request->code !== null) {
            $byCodeExpires = $request->expiredWarningUntil->getTimestamp() - time();
            $this->client->setex($byCodeKey, $byCodeExpires, $json);
        } else {
            $this->client->del($byCodeKey); // an old entry might still exist
        }

        // token lookup expires at warning time
        $byTokenKey = sprintf(self::PAIRING_REQUEST_BY_TOKEN_KEY_TEMPLATE, $request->token);
        $byTokenExpires = $request->expiredWarningUntil->getTimestamp() - time();
        $this->client->setex($byTokenKey, $byTokenExpires, $json);
    }

    /**
     * @inheritDoc
     */
    public function deletePairingRequest(IndexPairingRequest $request): void
    {
        $pairingRequestByTokenKey = sprintf(self::PAIRING_REQUEST_BY_TOKEN_KEY_TEMPLATE, $request->token);
        $this->client->del($pairingRequestByTokenKey);

        $pairingRequestByCodeKey = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $request->code);
        $this->client->del($pairingRequestByCodeKey);
    }
}
