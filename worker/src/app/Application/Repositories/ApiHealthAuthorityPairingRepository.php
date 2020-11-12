<?php
namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingResponse;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

/**
 * Health authority pairing gateway.
 *
 * @package App\Application\Repositories
 */
class ApiHealthAuthorityPairingRepository implements HealthAuthorityPairingRepository
{
    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GuzzleClient    $client
     * @param LoggerInterface $logger
     */
    public function __construct(GuzzleClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function completePairing(PairingRequest $request): PairingResponse
    {
        $this->logger->debug('Register client in health authority API');

        $options = [
            'json' => [
                'sealedClientPublicKey' => base64_encode($request->sealedClientPublicKey)
            ]
        ];

        $response = $this->client->post('cases/' . $request->case->id . '/clients', $options);
        $data = json_decode((string)$response->getBody());

        $sealedHealthAuthorityPublicKey = base64_decode($data->sealedHealthAuthorityPublicKey);
        return new PairingResponse($request, $sealedHealthAuthorityPublicKey);
    }
}
