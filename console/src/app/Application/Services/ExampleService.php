<?php
namespace App\Application\Services;

use App\Application\Models\Example;
use App\Application\Repositories\ExampleRepository;
use Exception;
use Psr\Log\LoggerInterface;

class ExampleService
{
    /**
     * @var ExampleRepository
     */
    private ExampleRepository $exampleRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param ExampleRepository $$exampleRepository
     * @param LoggerInterface   $logger
     */
    public function __construct(
        ExampleRepository $exampleRepository,
        LoggerInterface $logger
    )
    {
        $this->exampleRepository = $exampleRepository;
        $this->logger = $logger;
    }

    /**
     * Run the example.
     *
     * @throws Exception
     */
    public function example()
    {
        $this->logger->debug('Run example code');
        
        $example = $this->exampleRepository->createExample();
        // ...
        $this->exampleRepository->markExampleAsPrepared($example);
        // ...        
        $this->exampleRepository->markExampleAsExported($example);        
    }
}
