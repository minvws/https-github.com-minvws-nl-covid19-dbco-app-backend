<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;

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
        $request = $this->createRequest('PUT', '/v1/cases/1234');
        $request = $request->withParsedBody(new \stdClass);
        $request = $request->withHeader('Content-Type', 'application/json');
        $response = $this->app->handle($request);
        $this->assertEquals(204, $response->getStatusCode());
    }
}

