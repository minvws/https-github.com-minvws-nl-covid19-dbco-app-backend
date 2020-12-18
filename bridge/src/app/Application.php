<?php
namespace DBCO\Bridge;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Application.
 *
 * @package DBCO\Bridge\Application\Application
 */
class Application extends ConsoleApplication
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