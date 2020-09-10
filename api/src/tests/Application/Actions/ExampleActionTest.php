<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use App\Application\Models\Example;
use App\Application\Repositories\ExampleRepository;
use App\Application\Repositories\SimpleExampleRepository;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();
        $container = $app->getContainer();
        
        $exampleRepositoryMock = 
            $this->getMockBuilder(SimpleExampleRepository::class)
                ->setMethods(['createExample'])
                ->setConstructorArgs([new NullLogger()])
                ->getMock();
        
        $exampleRepositoryMock
            ->method('createExample')        
            ->willReturn(new Example('42'));
                
        $container->set(ExampleRepository::class, $exampleRepositoryMock);
        $container->set(LoggerInterface::class, new NullLogger());

        $request = $this->createRequest('GET', '/example');
        $response = $app->handle($request);

        $payload = json_decode((string)$response->getBody());
        $this->assertEquals('42', $payload->id);
        $this->assertEquals(Example::STATUS_PREPARED, $payload->status);
    }
}
