<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\GeneralTaskList;
use App\Application\Models\Header;
use App\Application\Models\QuestionnaireList;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;

/**
 * Retrieves the questionnaire list from the health authority API.
 */
class HAGetRepository implements QuestionnaireGetRepository, GeneralTaskGetRepository
{
    private const PROXY_HEADERS = ['Signature', 'Content-Length']; // TODO

    /**
     * @var GuzzleClient
     */
    private GuzzleClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param GuzzleClient    $client
     * @param LoggerInterface $logger
     */
    public function __construct(GuzzleClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getQuestionnaires(): QuestionnaireList
    {
        $this->logger->debug('Fetch questionnaire list from health authority API');

        $response = $this->client->get('questionnaires');

        $headers = [];
        foreach (self::PROXY_HEADERS as $name) {
            $values = $response->getHeader($name);
            if (count($values) > 0) {
                $headers[] = new Header($name, $values);
            }
        }

        $body = (string)$response->getBody();

        return new QuestionnaireList($headers, $body);
    }

    /**
     * @inheritDoc
     */
    public function getGeneralTasks(): GeneralTaskList
    {
        $this->logger->debug('Fetch general task list from health authority API');

        $response = $this->client->get('tasks');

        $headers = [];
        foreach (self::PROXY_HEADERS as $name) {
            $values = $response->getHeader($name);
            if (count($values) > 0) {
                $headers[] = new Header($name, $values);
            }
        }

        $body = (string)$response->getBody();

        return new GeneralTaskList($headers, $body);
    }
}
