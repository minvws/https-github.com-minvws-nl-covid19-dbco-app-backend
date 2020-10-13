<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use Exception;
use Tests\TestCase;

/**
 * List case tasks tests.
 *
 * @package Tests\Application\Actions
 */
class CaseTaskListActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testList()
    {
        $request = $this->createRequest('GET', '/v1/cases/1234/tasks');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}

