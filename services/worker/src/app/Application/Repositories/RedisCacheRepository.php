<?php
declare(strict_types=1);

namespace DBCO\Worker\Application\Repositories;

use DBCO\Worker\Application\Models\GeneralTaskList;
use DBCO\Worker\Application\Models\QuestionnaireList;
use Exception;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;

/**
 * Repository for storing questionnaires and general tasks from the health authority
 * API in a Redis cache.
 */
class RedisCacheRepository implements QuestionnaireCacheRepository, GeneralTaskCacheRepository
{
    const KEY_QUESTIONNAIRES = 'questionnaires';
    const KEY_TASKS = 'tasks';

    /**
     * Redis client.
     *
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PredisClient    $client
     * @param LoggerInterface $logger
     */
    public function __construct(PredisClient $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Store the questionnaire list in the cache.
     *
     * @param QuestionnaireList $questionnaires
     *
     * @throws Exception
     */
    public function putQuestionnaires(QuestionnaireList $questionnaires): void
    {
        $this->logger->debug('Store questionnaire list in Redis, key = ' . self::KEY_QUESTIONNAIRES);
        $this->client->set(self::KEY_QUESTIONNAIRES, json_encode($questionnaires));
    }

    /**
     * Store the general task list in the cache.
     *
     * @param GeneralTaskList $tasks
     *
     * @throws Exception
     */
    public function putGeneralTasks(GeneralTaskList $tasks): void
    {
        $this->logger->debug('Store general task list in Redis, key = ' . self::KEY_TASKS);
        $this->client->set(self::KEY_TASKS, json_encode($tasks));
    }
}
