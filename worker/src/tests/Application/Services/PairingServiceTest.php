<?php
declare(strict_types=1);

namespace DBCO\Worker\Tests\Application\Actions;

use DBCO\Worker\Application\Models\PairingRequest;
use DBCO\Worker\Application\Models\PairingResponse;
use DBCO\Worker\Application\Repositories\HealthAuthorityPairingRepository;
use DBCO\Worker\Application\Services\PairingService;
use DBCO\Worker\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * Pairing service tests.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
 */
class PairingServiceTest extends TestCase
{
    private const PAIRING_LIST_KEY = 'pairings';
    private const PAIRING_RESPONSE_LIST_KEY_TEMPLATE = 'pairing-response:%s';

    /**
     * Test pairing.
     */
    public function testProcessPairingQueue()
    {
        $stubHealthAuthorityPairingRepository =
            $this->createStub(HealthAuthorityPairingRepository::class);

        $stubHealthAuthorityPairingRepository
                ->method('completePairing')
                ->willReturnCallback(function (PairingRequest $request) {
                    return new PairingResponse($request, 'sealedHealthAuthorityPublicKey');
                });

        $this->getAppInstance()->getContainer()->set(HealthAuthorityPairingRepository::class, $stubHealthAuthorityPairingRepository);

        $pairingData = [
            'case' => [
                'id' => '12345',
            ],
            'sealedClientPublicKey' => base64_encode('sealedClientPublicKey')
        ];

        $responseKey = sprintf(self::PAIRING_RESPONSE_LIST_KEY_TEMPLATE, $pairingData['case']['id']);

        $redis = $this->getAppInstance()->getContainer()->get(PredisClient::class);
        $redis->rpush(self::PAIRING_LIST_KEY, [json_encode($pairingData)]);
        $this->assertEquals(0, $redis->exists($responseKey));

        $pairingService = $this->getAppInstance()->getContainer()->get(PairingService::class);
        $pairingService->processPairingQueue();

        $this->assertEquals(1, $redis->exists($responseKey));
        $data = $redis->lpop($responseKey);
        $this->assertNotNull($data);
        $data = json_decode($data);
        $this->assertNotNull($data->sealedHealthAuthorityPublicKey);
        $this->assertEquals(base64_encode('sealedHealthAuthorityPublicKey'), $data->sealedHealthAuthorityPublicKey);
    }
}
