<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use DBCO\HealthAuthorityAPI\Application\Models\Client;
use DBCO\HealthAuthorityAPI\Application\Models\ClientCase;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\ClientRepository;
use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Submit case tasks tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class CaseSubmitActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testSubmit()
    {
        $token = bin2hex(sodium_crypto_generichash(random_bytes(32)));
        $secretKey = sodium_crypto_secretbox_keygen();

        $client =
            new Client(
                $token,
                new ClientCase(Uuid::uuid4()->toString()),
                '',
                '',
                '',
                '',
                $secretKey,
                ''
            );

        $stubClientRepository = $this->createStub(ClientRepository::class);
        $stubClientRepository->method('getClient')->willReturnCallback(function ($t) use ($client, $token) {
            $this->assertEquals($token, $t);
            return $client;
        });

        $stubCaseRepository = $this->createStub(CaseRepository::class);
        $stubCaseRepository->method('caseExists')->willReturn(true);


        $container = $this->getAppInstance()->getContainer();
        $container->set(ClientRepository::class, $stubClientRepository);
        $container->set(CaseRepository::class, $stubCaseRepository);

        $data = [
            'dateOfSymptomOnset' => date('Y-m-d'),
            'tasks' => []
        ];
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox(json_encode($data), $nonce, $secretKey);

        $request = $this->createRequest('PUT', '/v1/cases/' . $token);
        $request = $request->withParsedBody([ 'sealedCase' => ['ciphertext' => base64_encode($ciphertext), 'nonce' => base64_encode($nonce)]]);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());
    }
}

