<?php

namespace MinVWS\DBCO\PairingRequest\Repositories;

use MinVWS\Codable\JSONDecoder;
use MinVWS\DBCO\PairingRequest\Models\HealthAuthorityPairingRequest;
use Predis\Client as PredisClient;

/**
 * Health authority pairing request storage/retrieval in/from Redis.
 */
class RedisHealthAuthorityPairingRequestRepository implements HealthAuthorityPairingRequestRepository
{
    private const PAIRING_REQUEST_BLOCKED_CODE_KEY_TEMPLATE = 'ha-pairing-request:blocked-code:%s';
    private const PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE      = 'ha-pairing-request:code:%s';
    private const PAIRING_REQUEST_BY_CASE_KEY_TEMPLATE      = 'ha-pairing-request:case:%s';

    // legacy, still need to check these for blocked codes
    private const PAIRING_REQUEST_BLOCKED_CODE_LEGACY_KEY_TEMPLATE = 'pairing-request:%s';

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
        $legacyKey = sprintf(self::PAIRING_REQUEST_BLOCKED_CODE_LEGACY_KEY_TEMPLATE, $code);

        // key expires automatically when the code becomes available again, so
        // if it still exists, we know it is not available
        return $this->client->exists($key) === 0 && $this->client->exists($legacyKey) === 0;
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
     * @return HealthAuthorityPairingRequest|null
     */
    private function decodePairingRequest(?string $json): ?HealthAuthorityPairingRequest
    {
        if ($json === null) {
            return null;
        }

        $decoder = new JSONDecoder();

        return
            $decoder
                ->decode($json)
                ->decodeObject(HealthAuthorityPairingRequest::class);
    }

    /**
     * @inheritDoc
     */
    public function getPairingRequestByCode(string $code): ?HealthAuthorityPairingRequest
    {
        $key = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $code);
        $json = $this->client->get($key);
        return $this->decodePairingRequest($json);
    }

    /**
     * @inheritDoc
     */
    public function getPairingRequestByCase(string $caseUuid): ?HealthAuthorityPairingRequest
    {
        $key = sprintf(self::PAIRING_REQUEST_BY_CASE_KEY_TEMPLATE, $caseUuid);
        $json = $this->client->get($key);
        return $this->decodePairingRequest($json);
    }

    /**
     * @inheritDoc
     */
    public function storePairingRequest(HealthAuthorityPairingRequest $request): void
    {
        $json = json_encode($request);

        // code lookup expires at warning time
        $byCodeKey = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $request->code);
        $byCodeExpires = $request->expiredWarningUntil->getTimestamp() - time();
        $this->client->setex($byCodeKey, $byCodeExpires, $json);

        // case lookup expires at warning time
        $byCaseKey = sprintf(self::PAIRING_REQUEST_BY_CASE_KEY_TEMPLATE, $request->caseUuid);
        $byCaseExpires = $request->expiredWarningUntil->getTimestamp() - time();
        $this->client->setex($byCaseKey, $byCaseExpires, $json);
    }

    /**
     * @inheritDoc
     */
    public function deletePairingRequest(HealthAuthorityPairingRequest $request): void
    {
        $pairingRequestByCodeKey = sprintf(self::PAIRING_REQUEST_BY_CODE_KEY_TEMPLATE, $request->code);
        $this->client->del($pairingRequestByCodeKey);

        $pairingRequestByCaseKey = sprintf(self::PAIRING_REQUEST_BY_CASE_KEY_TEMPLATE, $request->caseUuid);
        $this->client->del($pairingRequestByCaseKey);
    }
}
