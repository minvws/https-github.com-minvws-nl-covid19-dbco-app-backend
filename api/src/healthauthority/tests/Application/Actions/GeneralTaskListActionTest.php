<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;

/**
 * List general tasks tests.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class GeneralTaskListActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testList()
    {
        $request = $this->createRequest('GET', '/v1/tasks');
        $response = $this->app->handle($request);
        $this->assertResponseStatusCode(200, $response);
    }
}

