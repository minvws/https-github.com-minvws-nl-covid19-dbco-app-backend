<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use Exception;
use DBCO\PublicAPI\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * List general tasks tests.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
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
        try {
            $tasks = [
                'tasks' => []
            ];
            $tasksBody = json_encode($tasks);
            $tasksCacheData = [
                'headers' => [
                    ['name' => 'Content-Length', 'values' => [strlen($tasksBody)]],
                    ['name' => 'Signature', 'values' => ['test']]
                ],
                'body' => $tasksBody
            ];

            /** @var $redis PredisClient */
            $redis = $this->app->getContainer()->get(PredisClient::class);
            $redis->set('tasks', json_encode($tasksCacheData));

            $request = $this->createRequest('GET', '/v1/tasks');
            $response = $this->app->handle($request);

            $this->assertEquals(200, $response->getStatusCode());
            foreach ($tasksCacheData['headers'] as $header) {
                $this->assertEquals($header['values'], $response->getHeader($header['name']));
            }
            $this->assertEquals($tasksBody, (string)$response->getBody());
        } finally {
            /** @var $redis PredisClient */
            $redis = $this->app->getContainer()->get(PredisClient::class);
            $redis->del('tasks');
        }
    }
}

