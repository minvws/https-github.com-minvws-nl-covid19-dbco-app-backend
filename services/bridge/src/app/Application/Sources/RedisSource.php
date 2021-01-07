<?php
namespace DBCO\Bridge\Application\Sources;

use DateTimeImmutable;
use DBCO\Bridge\Application\Models\Request;
use DBCO\Bridge\Application\Models\Response;
use Predis\Client as PredisClient;
use Predis\Connection\ConnectionException;
use Psr\Log\LoggerInterface;

/**
 * Pairing gateway for the client.
 *
 * @package App\Application\Repositories
 */
class RedisSource implements Source
{
    /**
     * Redis client.
     *
     * @var PredisClient
     */
    private PredisClient $client;

    /**
     * Redis list key.
     *
     * @var string
     */
    private string $key;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PredisClient    $client
     * @param string          $key
     * @param LoggerInterface $logger
     */
    public function __construct(PredisClient $client, string $key, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->key = $key;
        $this->logger = $logger;
    }

    /**
     * Decode request.
     *
     * @param string $json
     *
     * @return array Request and response key [Request, string].
     */
    private function decodeRequest(string $json): array
    {
        $data = json_decode($json, true);

        $request = new Request(
            $data['params'] ?? [],
            $data['data'] ?? '',
            $data['originTraceId'] ?? null,
            isset($data['originSentAt']) ? new DateTimeImmutable($data['originSentAt']) : null,
        );

        $responseKey = $data['responseKey'] ?? null;

        return [$request, $responseKey];
    }

    /**
     * Encode response.
     *
     * @param Response $response
     *
     * @return string
     */
    private function encodeResponse(Response $response): string
    {
        $data = [
            'status' => $response->status,
            'data' => $response->data
        ];

        return json_encode($data);
    }

    /**
     * @inheritDoc
     */
    public function waitForRequest(callable $callback, int $timeout): bool
    {
        $end = time() + $timeout;

        while (time() < $end) {
            try {
                $result = $this->client->blpop($this->key, $end - time());
                if (!is_array($result) || count($result) !== 2) {
                    break; // timeout
                }

                $this->logger->debug('Received data for Redis key "' . $this->key . '"');

                [$request, $responseKey] = $this->decodeRequest($result[1]);

                /** @var Response $response */
                $response = $callback($request);

                if (!empty($responseKey)) {
                    $this->logger->debug('Push response to Redis key "' . $responseKey . '"');
                    $this->client->rpush($responseKey, [$this->encodeResponse($response)]);
                }

                return true;
            } catch (ConnectionException $e) {
                $this->logger->error('Redis connection error: ' . $e->getMessage());
                sleep(1); // maybe down, or connection timeout, wait a little and try again
            }
        }

        return false;
    }
}
