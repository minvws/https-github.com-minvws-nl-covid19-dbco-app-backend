<?php
namespace DBCO\Shared\Application;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

/**
 * Application.
 *
 * @package DBCO\Worker\Application\Application
 */
class ConsoleApplication extends Application
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Returns the container instance.
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Sets the container instance.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface  $container)
    {
        $this->container = $container;
    }
}