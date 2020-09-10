<?php
declare(strict_types=1);

namespace App\Application\Repositories;

use App\Application\Models\Example;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Repository.
 */
class SimpleExampleRepository implements ExampleRepository
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param PDO $connection The database connection
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Create new example model.
     *
     * @return Example Example model.
     *
     * @throws Exception
     */
    public function createExample(): Example
    {
        $example = new Example((string)rand());
        $this->logger->debug(sprintf('Create example %s', $example->id));
        return $example;        
    }

    /**
     * Mark example as prepared.
     *
     * @param Example $example
     *
     * @throws Exception
     */
    public function markExampleAsPrepared(Example $example): void
    {
        $this->logger->debug(sprintf('Mark example %s as prepared', $example->id));
        $example->status = Example::STATUS_PREPARED;
    }
        
    /**
     * Mark example as exported.
     *
     * @param Example $example
     *
     * @return void
     *
     * @throws Exception
     */
    public function markExampleAsExported(Example $example): void
    {
        $this->logger->debug(sprintf('Mark example %s as exported', $example->id));        
        $example->status = Example::STATUS_EXPORTED;
    }
}
