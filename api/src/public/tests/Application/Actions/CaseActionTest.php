<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use Exception;
use DBCO\PublicAPI\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * List case tasks tests.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
 */
class CaseActionTest extends TestCase
{
    private const CASE_TOKEN = 'b8436effb73da5b45c5b07bdf8de3bea';

    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testList()
    {
        $case = [
            'ciphertext' => base64_encode(random_bytes(1024)),
            'nonce' => base64_encode(random_bytes(20))
        ];

        $redis = $this->app->getContainer()->get(PredisClient::class);
        $redis->setex('case:' . self::CASE_TOKEN, 60, json_encode($case));

        $request = $this->createRequest('GET', '/v1/cases/' . self::CASE_TOKEN);
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string)$response->getBody();
        $data = json_decode($body);
        $this->assertIsObject($data);
        $this->assertIsObject($data->sealedCase);
        $this->assertEquals($case['ciphertext'], $data->sealedCase->ciphertext);
        $this->assertEquals($case['nonce'], $data->sealedCase->nonce);
    }
}

