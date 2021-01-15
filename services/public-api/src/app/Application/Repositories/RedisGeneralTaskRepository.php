<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\GeneralTaskList;
use DBCO\PublicAPI\Application\Models\Header;
use Predis\Client as PredisClient;
use RuntimeException;

/**
 * Used for retrieving general tasks from Redis.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class RedisGeneralTaskRepository implements GeneralTaskRepository
{
    const KEY_TASKS = 'tasks';

    /**
     * Redis client.
     *
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Constructor.
     *
     * @param PredisClient $client Redis client.
     */
    public function __construct(PredisClient $client)
    {
        $this->client = $client;
    }

    /**
     * Validate data.
     *
     * @param mixed $data
     */
    private function validateData($data)
    {
        if (!is_object($data)) {
            throw new RuntimeException('Task data invalid!');
        }

        if (!isset($data->headers) || !is_array($data->headers)) {
            throw new RuntimeException('Task headers invalid!');
        } else {
            foreach ($data->headers as $header) {
                if (empty($header->name) || !is_string($header->name)) {
                    throw new RuntimeException('Task headers invalid!');
                } else if (!isset($header->values) || !is_array($header->values)) {
                    throw new RuntimeException('Task headers invalid!');
                }
            }
        }

        if (empty($data->body) || !is_string($data->body)) {
            throw new RuntimeException('Task body invalid!');
        }
    }

    /**
     * Returns the general task list.
     *
     * @return GeneralTaskList
     */
    public function getGeneralTasks(): GeneralTaskList
    {
        $json = $this->client->get(self::KEY_TASKS);
        if ($json === null) {
            throw new RuntimeException('Tasks not available in Redis!');
        }

        $data = @json_decode($json);
        $this->validateData($data);

        $headers = [];
        foreach ($data->headers as $rawHeader) {
            $headers[] = new Header($rawHeader->name, $rawHeader->values);
        }

        $body = $data->body;

        return new GeneralTaskList($headers, $body);
    }
}
