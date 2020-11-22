<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\Shared\Application\DTO\SealedData as SealedDataDTO;
use DBCO\Shared\Application\Models\SealedData;
use Predis\Client as PredisClient;
use RuntimeException;
use Throwable;

/**
 * Used for retrieving and submitting cases from/to Redis.
 *
 * @package DBCO\PublicAPI\Application\Repositories
 */
class RedisCaseRepository implements CaseRepository
{
    const CASE_KEY_TEMPLATE     = 'case:%s'; // case:<token>
    const CASE_RESULT_LIST_KEY = 'caseresults';

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
        if (!is_object($data) || !isset($data->sealedCase->ciphertext) || !isset($data->sealedCase->nonce)) {
            throw new RuntimeException('Case data invalid!');
        }
    }

    /**
     * @inheritDoc
     */
    public function caseExists(string $token): bool
    {
        $key = sprintf(self::CASE_KEY_TEMPLATE, $token);
        return $this->client->exists($key) === 1;
    }

    /**
     * @inheritDoc
     */
    public function getCase(string $token): ?SealedData
    {
        $key = sprintf(self::CASE_KEY_TEMPLATE, $token);
        $json = $this->client->get($key);
        if ($json === null) {
            return null;
        }

        $data = @json_decode($json);
        $this->validateData($data);

        return SealedDataDTO::jsonUnserialize($data->sealedCase);
    }
}
