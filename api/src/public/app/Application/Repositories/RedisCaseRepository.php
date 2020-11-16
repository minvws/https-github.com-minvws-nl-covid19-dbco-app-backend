<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\SealedCase;
use Predis\Client as PredisClient;
use RuntimeException;

/**
 * Used for retrieving cases from Redis.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class RedisCaseRepository implements CaseRepository
{
    const CASE_KEY_TEMPLATE = 'case:%s'; // case:<token>

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
        if (!is_object($data) || !isset($data->ciphertext) || !isset($data->nonce)) {
            throw new RuntimeException('Case data invalid!');
        }
    }

    /**
     * @inheritDoc
     */
    public function getCase(string $token): SealedCase
    {
        $key = sprintf(self::CASE_KEY_TEMPLATE, $token);
        $json = $this->client->get($key);
        if ($json === null) {
            throw new RuntimeException('Case not available in Redis!');
        }

        $data = @json_decode($json);
        $data = json_decode($data->payload); // TODO: fix me correctly
        //$this->validateData($data);

        return new SealedCase(
            base64_decode($data->sealedCase->ciphertext),
            base64_decode($data->sealedCase->nonce)
        );
    }

    /**
     * @inheritDoc
     */
    public function submitCase(string $token, SealedCase $sealedCase): void
    {
    }
}
