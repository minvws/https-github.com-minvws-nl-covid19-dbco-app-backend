<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use DBCO\PublicAPI\Tests\TestCase;

/**
 * Config test.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
 */
class ConfigActionTest extends TestCase
{
    /**
     * Test config retrieval.
     */
    public function testConfig()
    {
        $request = $this->createRequest('GET', '/v1/config');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $json = (string)$response->getBody();
        $config = json_decode($json, true);

        $expectedKeys = [
            'androidMinimumVersion' => 'is_int',
            'androidMinimumVersionMessage' => 'is_string',
            'iosMinimumVersion' => 'is_string',
            'iosMinimumVersionMessage' => 'is_string',
            'iosAppStoreURL' => 'is_string'
        ];

        foreach ($expectedKeys as $key => $typeCheck) {
            $this->assertArrayHasKey($key, $config);
            $this->assertTrue($typeCheck($config[$key]));
        }
    }
}

