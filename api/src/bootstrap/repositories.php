<?php
declare(strict_types=1);

use App\Application\Repositories\ExampleRepository;
use App\Application\Repositories\SimpleExampleRepository;
use App\Application\Repositories\CaseRepository;
use App\Application\Repositories\DbCaseRepository;
use App\Application\Helpers\RandomKeyGeneratorInterface;
use Psr\Container\ContainerInterface;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;
use function DI\env;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        ExampleRepository::class => autowire(SimpleExampleRepository::class),
        CaseRepository::class => function (ContainerInterface $c) {
            $maxKeyGenerationAttempts = $settings = $c->get('settings')['maxKeyGenerationAttempts'];

            return new  DbCaseRepository(
                $c->get('PDO'),
                $c->get(RandomKeyGeneratorInterface::class),
                $maxKeyGenerationAttempts
            );
        }
    ]);
};
