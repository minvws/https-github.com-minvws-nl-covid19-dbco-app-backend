<?php
declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Tests\Application\Actions;

use Exception;
use DBCO\HealthAuthorityAPI\Tests\TestCase;

/**
 * List questionnaires test.
 *
 * @package DBCO\HealthAuthorityAPI\Tests\Application\Actions
 */
class QuestionnaireListActionTest extends TestCase
{
    /**
     * Test happy flow.
     *
     * @throws Exception
     */
    public function testList()
    {
        $request = $this->createRequest('GET', '/v1/questionnaires');
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}

