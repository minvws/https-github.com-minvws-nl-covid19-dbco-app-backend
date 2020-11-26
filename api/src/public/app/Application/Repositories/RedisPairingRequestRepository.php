<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DateTime;
use DBCO\PublicAPI\Application\Exceptions\PairingRequestExpiredException;
use DBCO\PublicAPI\Application\Exceptions\PairingRequestNotFoundException;
use DBCO\Shared\Application\Codable\JSONDecoder;
use Predis\Client as PredisClient;

/**
 * Used to complete pairing requests using Redis.
 *
 * @package DBCO\PrivateAPI\Application\Repositories
 */
class RedisPairingRequestRepository implements PairingRequestRepository
{
    private const PAIRING_REQUEST_KEY_TEMPLATE      = 'pairing-request:%s';
    private const PAIRING_REQUEST_CASE_KEY_TEMPLATE = 'pairing-request:%s:case';
    private const CASE_PAIRING_REQUEST_KEY_TEMPLATE = 'case:%s:pairing-request';

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
    public function completePairingRequest(string $code): string
    {
        // retrieve and delete pairing request case data
        $pairingRequestKey = sprintf(self::PAIRING_REQUEST_KEY_TEMPLATE, $code);
        $pairingRequestCaseKey = sprintf(self::PAIRING_REQUEST_CASE_KEY_TEMPLATE, $code);
        [$pairingRequestJson, $pairingRequestCaseJson, $del] =
            $this->client->transaction()
                ->get($pairingRequestKey)
                ->get($pairingRequestCaseKey)
                ->del($pairingRequestCaseKey)
                ->execute();

        // check if we there still was case pairing request data
        if ($pairingRequestJson === null || $pairingRequestCaseJson === null || $del === 0) {
            throw new PairingRequestNotFoundException();
        }

        $decoder = new JSONDecoder();

        $container = $decoder->decode($pairingRequestCaseJson);
        $caseUuid = $container->caseUuid->decodeString();
        $codeExpiresAt = $container->codeExpiresAt->decodeDateTime(DateTime::ATOM);
        $codeExpiredWarningUntil = $container->codeExpiredWarningUntil->decodeDateTime(DateTime::ATOM);

        $casePairingRequestKey = sprintf(self::CASE_PAIRING_REQUEST_KEY_TEMPLATE, $caseUuid);
        $this->client->del($casePairingRequestKey);

        $now = new DateTime();

        if ($codeExpiresAt > $now) {
            // code valid
            return $caseUuid;
        }

        if ($codeExpiredWarningUntil > $now) {
            // allow (one-time) warning
            throw new PairingRequestExpiredException();
        }

        // not found
        throw new PairingRequestNotFoundException();
    }
}
