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
    public function isPairingCodeAvailable(string $code): bool
    {
        $pairingRequestKey = sprintf(self::PAIRING_REQUEST_KEY_TEMPLATE, $code);

        // key expires automatically when the code becomes available again, so
        // if it still exists, we know it is not available
        return $this->client->exists($pairingRequestKey) === 0;
    }

    /**
     * @inheritdoc
     */
    public function disableActivePairingCodeForCase(string $caseUuid)
    {
        // lookup pairing code (if any)
        $casePairingRequestKey = sprintf(self::CASE_PAIRING_REQUEST_KEY_TEMPLATE, $caseUuid);
        $data = $this->client->get($casePairingRequestKey);
        if (!$data) {
            return;
        }

        // delete pairing code lookup data
        $this->client->del($casePairingRequestKey);

        // delete case data for pairing request (not the request itself, so the code remains blocked)
        $data = json_decode($data);
        $pairingRequestCaseKey = sprintf(self::PAIRING_REQUEST_CASE_KEY_TEMPLATE, $data->code);

        $this->client->del($pairingRequestCaseKey);
    }

    /**
     * @inheritDoc
     */
    public function storePairingRequest(PairingRequest $request)
    {
        // store the main pairing request data, this data will be kept until
        // the pairing code should be become available again
        $pairingRequestKey = sprintf(self::PAIRING_REQUEST_KEY_TEMPLATE, $request->code);
        $pairingRequestExpires = $request->codeBlockedUntil->getTimestamp() - time();
        $pairingRequestData = [
            'codeBlockedUntil' => $request->codeBlockedUntil->format(DateTime::ATOM)
        ];
        $this->client->setex($pairingRequestKey, $pairingRequestExpires, json_encode($pairingRequestData));

        // store the case identifier for the duration of the validity of the pairing code
        $pairingRequestCaseKey = sprintf(self::PAIRING_REQUEST_CASE_KEY_TEMPLATE, $request->code);
        $pairingRequestCaseExpires = $request->codeExpiredWarningUntil->getTimestamp() - time();
        $pairingRequestCaseData = [
            'caseUuid' => $request->caseUuid,
            'codeExpiresAt' => $request->codeExpiresAt->format(DateTime::ATOM),
            'codeExpiredWarningUntil' => $request->codeExpiredWarningUntil->format(DateTime::ATOM)
        ];
        $this->client->setex($pairingRequestCaseKey, $pairingRequestCaseExpires, json_encode($pairingRequestCaseData));

        // store a reference from the case identifier to the pairing code so we can disable
        // existing pairing requests for a case when a new pairing request is generated
        $casePairingRequestKey = sprintf(self::CASE_PAIRING_REQUEST_KEY_TEMPLATE, $request->caseUuid);
        $casePairingRequestExpires = $request->codeExpiredWarningUntil->getTimestamp() - time();
        $casePairingRequestData = [
            'code' => $request->code
        ];
        $this->client->setex($casePairingRequestKey, $casePairingRequestExpires, json_encode($casePairingRequestData));
    }
}
