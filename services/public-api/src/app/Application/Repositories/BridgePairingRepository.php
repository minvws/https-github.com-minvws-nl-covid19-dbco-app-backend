<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\Pairing;
use DBCO\Shared\Application\Bridge\BridgeException;
use DBCO\Shared\Application\Bridge\RequestException as BridgeRequestException;
use DBCO\Shared\Application\Bridge\Client as BridgeClient;
use DBCO\Shared\Application\Bridge\Request as BridgeRequest;
use RuntimeException;

/**
 * Store/retrieve pairings via the bridge.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class BridgePairingRepository implements PairingRepository
{
    private const PAIRING_LIST_KEY = 'clients';
    private const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'client-response:%s';
    private const PAIRING_TIMEOUT = 30;

    /**
     * @var BridgeClient
     */
    private BridgeClient $client;

    /**
     * Constructor.
     *
     * @param BridgeClient $client
     */
    public function __construct(BridgeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function storePairing(Pairing $pairing)
    {
        $requestData = [
            'sealedClientPublicKey' => base64_encode($pairing->sealedClientPublicKey)
        ];

        $request =
            BridgeRequest::create(self::PAIRING_LIST_KEY)
                ->setResponseKey(sprintf(self::PAIRING_RESPONSE_LIST_KEY_TEMPLATE, $pairing->caseUuid))
                ->setParam('caseUuid', $pairing->caseUuid)
                ->setData(json_encode($requestData))
                ->setTimeout(self::PAIRING_TIMEOUT);

        try {
            $response = $this->client->request($request);
            $responseData = json_decode($response->getData());
            $pairing->sealedHealthAuthorityPublicKey =
                base64_decode($responseData->sealedHealthAuthorityPublicKey);
        } catch (BridgeRequestException $e) {
            $message = json_decode($e->getResponse()->getData())->message ?? $e->getMessage();
            throw new RuntimeException('Pairing failed: ' . $message, 0, $e);
        } catch (BridgeException $e) {
            throw new RuntimeException('Pairing failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
