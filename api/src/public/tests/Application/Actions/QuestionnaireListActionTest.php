<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Tests\Application\Actions;

use Exception;
use DBCO\PublicAPI\Tests\TestCase;
use Predis\Client as PredisClient;

/**
 * List questionnaires test.
 *
 * @package DBCO\PublicAPI\Tests\Application\Actions
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
        try {
            $questionnaires = [
                'questionnaires' => [
                    [
                        'id' => 'dummy',
                        'taskType' => 'contact'
                    ]
                ]
            ];
            $questionnairesBody = json_encode($questionnaires);
            $questionnairesCacheData = [
                'headers' => [
                    ['name' => 'Content-Length', 'values' => [strlen($questionnairesBody)]],
                    ['name' => 'Signature', 'values' => ['test']]
                ],
                'body' => $questionnairesBody
            ];

            /** @var $redis PredisClient */
            $redis = $this->app->getContainer()->get(PredisClient::class);
            $redis->set('questionnaires', json_encode($questionnairesCacheData));

            $request = $this->createRequest('GET', '/v1/questionnaires');
            $response = $this->app->handle($request);

            $this->assertEquals(200, $response->getStatusCode());
            foreach ($questionnairesCacheData['headers'] as $header) {
                $this->assertEquals($header['values'], $response->getHeader($header['name']));
            }
            $this->assertEquals($questionnairesBody, (string)$response->getBody());
        } finally {
            /** @var $redis PredisClient */
            $redis = $this->app->getContainer()->get(PredisClient::class);
            $redis->del('questionnaires');
        }
    }
}

