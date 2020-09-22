<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Services\ExampleService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ExampleAction extends Action
{
    protected ExampleService $exampleService;

    /**
     * @param LoggerInterface $logger
     * @param PaddingGeneratorInterface $paddingGenerator
     */
    public function __construct(
        LoggerInterface $logger,
        ExampleService $exampleService
    )
    {
        parent::__construct($logger);
        $this->exampleService = $exampleService;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $example = $this->exampleService->example();
        $this->response->getBody()->write(json_encode($example));            
        return $this->response->withHeader('Content-Type', 'application/json');
    }
}
