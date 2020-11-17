<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use Exception;
use DBCO\PublicAPI\Tests\TestCase;

/**
 * Submit case tasks tests.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
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
        $body = [
            'sealedCase' => [
                'ciphertext' => base64_encode(random_bytes(1024)),
                'nonce' => base64_encode(random_bytes(20))
            ]
        ];
        $request = $this->createRequest('PUT', '/v1/cases/1234');
        $request = $request->withParsedBody($body);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());
    }
}

