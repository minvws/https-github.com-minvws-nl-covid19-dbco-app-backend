<?php
declare(strict_types=1);

namespace DBCO\Bridge\Tests\Application\Actions;

use DBCO\Bridge\Application\Models\Request;
use DBCO\Bridge\Application\Models\Response;
use DBCO\Bridge\Application\Repositories\HealthAuthorityPairingRepository;
use DBCO\Bridge\Application\Services\BridgeService;
use DBCO\Bridge\Tests\TestCase;
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
                ->willReturnCallback(function (Request $request) {
                    return new Response($request, 'sealedHealthAuthorityPublicKey');
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
        $this->assertEquals(1, $redis->exists(self::PAIRING_LIST_KEY));
        $this->assertEquals(0, $redis->exists($responseKey));

        $pairingService = $this->getAppInstance()->getContainer()->get(BridgeService::class);
        $pairingService->processPairingQueueEntry(5);

        $this->assertEquals(1, $redis->exists($responseKey));
        $data = $redis->lpop($responseKey);
        $this->assertNotNull($data);
        $data = json_decode($data);
        $this->assertNotNull($data->sealedHealthAuthorityPublicKey);
        $this->assertEquals(base64_encode('sealedHealthAuthorityPublicKey'), $data->sealedHealthAuthorityPublicKey);
    }
}
