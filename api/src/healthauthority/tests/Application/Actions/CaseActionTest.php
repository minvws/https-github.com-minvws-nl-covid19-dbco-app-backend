<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;

/**
 * List case tasks tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class CaseActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testList()
    {
        $request = $this->createRequest('GET', '/v1/cases/1234');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}

